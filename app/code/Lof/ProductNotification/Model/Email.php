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

namespace Lof\ProductNotification\Model;

class Email extends \Magento\Framework\Model\AbstractModel
{
    const XML_PATH_EMAIL_PRICE_TEMPLATE = 'productnotification/productalert/email_price_template';

    const XML_PATH_EMAIL_STOCK_TEMPLATE = 'productnotification/productalert/email_stock_template';

    const XML_PATH_EMAIL_NEW_PRODUCT_TEMPLATE = 'productnotification/productalert/email_new_product_template';

    const XML_PATH_EMAIL_IDENTITY = 'productnotification/productalert/email_identity';

    /**
     * Type
     *
     * @var string
     */
    protected $_type = 'price';

    /**
     * Website Model
     *
     * @var \Magento\Store\Model\Website
     */
    protected $_website;

    /**
     * Customer model
     *
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $_customer;

    /**
     * Products collection where changed price
     *
     * @var array
     */
    protected $_priceProducts = [];

    /**
     * Product collection which of back in stock
     *
     * @var array
     */
    protected $_stockProducts = [];
    /**
     * New Product collection
     *
     * @var array
     */
    protected $_newProducts = [];

    /**
     * Price block
     *
     * @var \Lof\ProductNotification\Block\Email\Price
     */
    protected $_priceBlock;

    /**
     * Stock block
     *
     * @var \Lof\ProductNotification\Block\Email\Stock
     */
    protected $_stockBlock;

    /**
     * New Product block
     *
     * @var \Lof\ProductNotification\Block\Email\NewProduct
     */
    protected $_newproductBlock;

    /**
     * Product alert data
     *
     * @var \Lof\ProductNotification\Helper\Data
     */
    protected $_productNotificationData = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $_appEmulation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerHelper;

    /**
     * Email constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Lof\ProductNotification\Helper\Data $productNotificationData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Helper\View $customerHelper
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\ProductNotification\Helper\Data $productNotificationData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Helper\View $customerHelper,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_productNotificationData = $productNotificationData;
        $this->_scopeConfig             = $scopeConfig;
        $this->_storeManager            = $storeManager;
        $this->customerRepository       = $customerRepository;
        $this->_appEmulation            = $appEmulation;
        $this->_transportBuilder        = $transportBuilder;
        $this->_customerHelper          = $customerHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Set model type
     *
     * @param string $type
     * @return void
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * Retrieve model type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set website model
     *
     * @param \Magento\Store\Model\Website $website
     * @return $this
     */
    public function setWebsite(\Magento\Store\Model\Website $website)
    {
        $this->_website = $website;
        return $this;
    }

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->_website = $this->_storeManager->getWebsite($websiteId);
        return $this;
    }

    /**
     * Set customer by id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->_customer = $this->customerRepository->getById($customerId);
        return $this;
    }

    /**
     * Set customer model
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return $this
     */
    public function setCustomerData($customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Clean data
     *
     * @return $this
     */
    public function clean()
    {
        $this->_customer = null;
        $this->_priceProducts = [];
        $this->_stockProducts = [];
        $this->_newProducts = [];

        return $this;
    }

    /**
     * Add product (price change) to collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function addPriceProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->_priceProducts[$product->getId()] = $product;
        return $this;
    }

    /**
     * Add product (back in stock) to collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function addStockProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->_stockProducts[$product->getId()] = $product;
        return $this;
    }

     /**
     * Add new product to collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function addNewProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->_newProducts[$product->getId()] = $product;
        return $this;
    }

    /**
     * Retrieve price block
     *
     * @return \Lof\ProductNotification\Block\Email\Price
     */
    protected function _getPriceBlock()
    {
        if ($this->_priceBlock === null) {
            $this->_priceBlock = $this->_productNotificationData->createBlock('Lof\ProductNotification\Block\Email\Price');
        }
        $this->_priceBlock->setAlert($this->getAlert());
        return $this->_priceBlock;
    }

    /**
     * Retrieve price block
     *
     * @return \Lof\ProductNotification\Block\Email\NewProduct
     */
    protected function _getNewProductBlock()
    {
        if ($this->_newproductBlock === null) {
            $this->_newproductBlock = $this->_productNotificationData->createBlock('Lof\ProductNotification\Block\Email\NewProduct');
        }
        $this->_newproductBlock->setAlert($this->getAlert());
        return $this->_newproductBlock;
    }

    /**
     * Retrieve stock block
     *
     * @return \Lof\ProductNotification\Block\Email\Stock
     */
    protected function _getStockBlock()
    {
        if ($this->_stockBlock === null) {
            $this->_stockBlock = $this->_productNotificationData->createBlock('Lof\ProductNotification\Block\Email\Stock');
        }
        $this->_stockBlock->setAlert($this->getAlert());
        return $this->_stockBlock;
    }

    /**
     * Send customer email
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function send()
    {
        if ($this->_website === null) {
            return false;
        }
        if ($this->_type == 'price' && count(
            $this->_priceProducts
        ) == 0 || $this->_type == 'stock' && count(
            $this->_stockProducts
        ) == 0 || $this->_type == 'new_product' && count(
            $this->_newProducts
        ) == 0
        ) {
            return false;
        }
        if (!$this->_website->getDefaultGroup() || !$this->_website->getDefaultGroup()->getDefaultStore()) {
            return false;
        }
        $store = $this->_website->getDefaultStore();
        $storeId = $store->getId();

        if ($this->_type == 'price' && !$this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_PRICE_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        )) {
            return false;
        } elseif ($this->_type == 'stock' && !$this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_STOCK_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        )){
           return false; 
        } elseif ($this->_type == 'new_product' && !$this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_NEW_PRODUCT_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        )){
            return false;
        }

        if ($this->_type != 'price' && $this->_type != 'stock' && $this->_type != 'new_product') {
            return false;
        }

        $this->_appEmulation->startEnvironmentEmulation($storeId);

        if ($this->_type == 'price') {
            $this->_getPriceBlock()->setStore($store)->reset();
            foreach ($this->_priceProducts as $product) {
                $this->_getPriceBlock()->addProduct($product);
            }
            $block = $this->_getPriceBlock();
            $templateId = $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_PRICE_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } elseif ($this->_type == 'new_product') {
            $this->_getNewProductBlock()->setStore($store)->reset();
            foreach ($this->_newProducts as $product) {
                $this->_getNewProductBlock()->addProduct($product);
            }
            $block = $this->_getNewProductBlock();
            $templateId = $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_PRICE_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } else {
            $this->_getStockBlock()->setStore($store)->reset();
            foreach ($this->_stockProducts as $product) {
                $this->_getStockBlock()->addProduct($product);
            }
            $block = $this->_getStockBlock();
            $templateId = $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_STOCK_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        $alertGrid = $this->_appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_FRONTEND,
            [$block, 'toHtml']
        );

        $this->_appEmulation->stopEnvironmentEmulation();

        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $templateId
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            [
                'customerName' => $this->getSubscriberName(),
                'alertGrid' => $alertGrid,
            ]
        )->setFrom(
            $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        )->addTo(
            $this->getSubscriberEmail(),
            $this->getSubscriberName()
        )->getTransport();

        $transport->sendMessage();

        return true;
    }
}
