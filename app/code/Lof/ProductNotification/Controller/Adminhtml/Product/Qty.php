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

namespace Lof\ProductNotification\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ProductRepository as Product;

class Qty extends \Magento\Backend\App\Action
{

    /**
     * @var Product
     */
    private $product;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * InlineEdit constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Product $product
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Product $product
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->product = $product;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $data = $this->getRequest()->getParams();

        if (!empty($data['selected'])) {
            foreach ($data['selected'] as $productId) {
                $product = $this->product->getById($productId);
                try {
                    $data['quantity_and_stock_status'] = $product->getData('quantity_and_stock_status');
                    $data['quantity_and_stock_status']['qty'] = $data['input'];
                    $product->setData(array_merge($product->getData(), $data));
                    $product->setQty($data['input']);
                    $extendedAttributes = $product->getExtensionAttributes();
                    $stockItem = $extendedAttributes->getStockItem();
                    $stockItem->setQty($data['input']);
                    $this->product->save($product);
                } catch (\Exception $e) {
                    $messages[] = $this->getErrorWithProductId(
                        $product,
                        __($e->getMessage())
                    );
                    $error = true;
                }
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('catalog/*/');
        return $resultRedirect;
    }
}
