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

class Price extends \Magento\Framework\View\Element\Template
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
        $this->_registry         = $registry;
        $this->_customerSession  = $customerSession;
        $this->helperData        = $helperData;
    }

    /**
     * Prepare price info
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        if(!$this->helperData->getConfig('productalert/allow_price')) {
            $template = '';
            return parent::setTemplate($template);
        }
        if (!$this->_customerSession->isLoggedIn() && $this->helperData->getConfig('productalert/disable_price_guest')) {
            $template = '';
            return parent::setTemplate($template);
        }


        if ($this->_customerSession->isLoggedIn()) {
            $customer  = $this->_customerSession->getCustomer();
            $product   = $this->getProduct();
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            if ($this->helperData->isNotificationExit('lof_product_notification_price', $customer->getId(), $product->getId(), $websiteId)) {
                $template = '';;
            }
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
