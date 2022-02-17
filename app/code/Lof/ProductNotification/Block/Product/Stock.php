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

namespace Lof\ProductNotification\Block\Product;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Stock extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\ProductNotification\Helper\Data $helperData,
        array $data = []
    ) {
        parent::__construct($context);
        $this->_registry        = $registry;
        $this->_customerSession = $customerSession;
        $this->helperData       = $helperData;
        $this->pageConfig       = $context->getPageConfig();
    }

    /**
     * Prepare stock info
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        if(!$this->helperData->getConfig('productalert/allow_stock')) {
            $template = '';
            return parent::setTemplate($template);
        }
        if (!$this->getProduct() || $this->getProduct()->isAvailable()) {
            $template = '';
        }

        $product = $this->getProduct();
        if ($product) {
            $this->pageConfig->addBodyClass('pnf-product-' . $product->getTypeId());
        }

        if (!$this->_customerSession->isLoggedIn() && $this->helperData->getConfig('productalert/disable_stock_guest')) {
            $template = '';
            return parent::setTemplate($template);
        }        

        if ($this->_customerSession->isLoggedIn()) {
            $customer  = $this->_customerSession->getCustomer();
            $product   = $this->getProduct();
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $exists = $this->helperData->isNotificationExit('lof_product_notification_stock', $customer->getId(), $product->getId(), $websiteId);
            if ($exists) {
                $template = '';
                return parent::setTemplate($template);
            }
        }
        if ($product && $product->getTypeId()==Configurable::TYPE_CODE) {
            //$template = 'product/stock.phtml';
            $template = '';
        }
        return parent::setTemplate($template);
    }

    /**
     * Retrieve currently edited product object
     *
     * @return \Magento\Catalog\Model\Product|boolean
     */
    public function getProduct()
    {
        $product = $this->_registry->registry('current_product');
        if ($product && $product->getId()) {
            return $product;
        }
        return false;
    }
}