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

namespace Lof\ProductNotification\Block\Customer;

class Price extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Lof\Formbuilder\Model\Message
     */
    protected $_message;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context         
     * @param \Lof\Formbuilder\Model\Message                   $message         
     * @param \Magento\Customer\Model\Session                  $customerSession 
     * @param array                                            $data            
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Lof\ProductNotification\Model\ResourceModel\Price\CollectionFactory $stockCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Data\Helper\PostHelper $postHelper,
        array $data = []
    ) {
        parent::__construct($context);
        $this->stockCollectionFactory = $stockCollectionFactory;
        $this->customerSession        = $customerSession;
        $this->postHelper             = $postHelper;
        $this->imageBuilder           = $imageBuilder;
    }

    public function getCustomer($customerId = '')
    {	
        $customer = $this->customerSession->getCustomer();
        return $customer;
    }

    public function _toHtml() {
        $grid_pagination = true;
        $item_per_page   = 5;
        $collection      = $this->stockCollectionFactory->create();
        $website         = $this->_storeManager->getStore()->getWebsite();
        $collection->addFieldToFilter('customer_id', $this->getCustomer()->getId())
        ->addWebsiteFilter($website)
        ->getSelect()
        ->order('alert_price_id DESC');
        if($grid_pagination){
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager','my.custom.pager');
            $pager->setLimit($item_per_page)->setCollection($collection);
            $this->setChild('pager', $pager);
        }
        $this->setCollection($collection);
        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param AbstractCollection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_postCollection = $collection;
        return $this;
    }

    public function getCollection()
    {
        return $this->_postCollection;
    }

    public function getConfig($key, $default = '')
    {
        if($this->hasData($key)){
            return $this->getData($key);
        }
        return $default;
    }

    public function getPostDataParams($product)
    {
        return $this->postHelper->getPostData($this->getDeleteUrl(), ['product' => $product->getId()]);
    }

    /**
     * Retrieve url for adding product to compare list
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('productnotification/price/delete');
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }
}