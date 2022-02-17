<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_ProductNotification
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\ProductNotification\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productloadafter implements ObserverInterface
{
    protected $catalogSession;
    protected $sourceDataBySku;

    public function __construct(
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku
    )
    {
        $this->catalogSession = $catalogSession;
        $this->sourceDataBySku = $sourceDataBySku;
    }

    public function getSourceStocks($_product_sku){
        return $this->sourceDataBySku->execute($_product_sku);
        //  (
        //      [0] => Array
        //         (
        //             [source_code] => test_source
        //             [quantity] => 50
        //             [status] => 1
        //             [name] => Test Source
        //             [source_status] => 1
        //         )
        
        //     [1] => Array
        //         (
        //             [source_code] => test_source2
        //             [quantity] => 80
        //             [status] => 1
        //             [name] => Test Source 2
        //             [source_status] => 1
        //         )
        
        // )
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product = $observer->getProduct();
        $source_stocks = $this->getSourceStocks($_product->getSku());

        $this->catalogSession->setOldPrice($_product->getPrice());
        $this->catalogSession->setOldSpecialPrice($_product->getSpecialPrice());
        $this->catalogSession->setOldSpecialFromDate($_product->getSpecialFromDate());//special_from_date
        $this->catalogSession->setOldSpecialToDate($_product->getSpecialToDate());//special_to_date

        $qty = $_product->getQty();
        $qty_stock = $_product->getQuantityAndStockStatus();
        $stock_status = 0;
        if(!$qty_stock) {
            if(isset($_product['quantity_and_stock_status']) && is_array($_product['quantity_and_stock_status']) && $_product['quantity_and_stock_status']){
                if(isset($_product['quantity_and_stock_status']['qty']) && $_product['quantity_and_stock_status']['qty']){
                    $qty = $_product['quantity_and_stock_status']['qty'];
                    if($qty > 0){
                        $stock_status = 1;
                    }
                }
            }
        } elseif($source_stocks){
            $qty = 0;
            foreach($source_stocks as $_source){
                if($_source["status"] && $_source["source_status"]){
                    $stock_status = 1;
                    $qty += (int)$_source["quantity"];
                }
            }
        }else {
            if(isset($qty_stock['qty']) && $qty_stock['qty']){
                $qty = $qty_stock['qty'];
                if($qty > 0){
                    $stock_status = 1;
                }
            }
        }
        $this->catalogSession->setOldQty($qty);
        $this->catalogSession->setOldProductQty($qty);
        $this->catalogSession->setOldInStockStatus($stock_status);
    }
}