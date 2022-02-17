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
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Productsaveafter implements ObserverInterface
{
    /**
     * Error email template configuration
     */
    const XML_PATH_ERROR_TEMPLATE = 'productnotification/productalert_cron/error_email_template';

    /**
     * Error email identity configuration
     */
    const XML_PATH_ERROR_IDENTITY = 'productnotification/productalert_cron/error_email_identity';

    /**
     * 'Send error emails to' configurationproductnotification_productalert_email_stock_template
     */
    const XML_PATH_ERROR_RECIPIENT = 'productnotification/productalert_cron/error_email';

    /**
     * Allow price alert
     *
     */
    const XML_PATH_PRICE_ALLOW = 'productnotification/productalert/allow_price';

    /**
     * Allow price alert
     *
     */
    const XML_PATH_NEW_PRODUCT_ALLOW = 'productnotification/productalert/allow_new_product';

    /**
     * Allow stock alert
     *
     */
    const XML_PATH_STOCK_ALLOW = 'productnotification/productalert/allow_stock';

    const XML_PATH_ENABLE = 'productnotification/general_settings/enable';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;
    /**
     * Warning (exception) errors array
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $_dateFactory;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Subscribers collection
     *
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection
     */
    protected $_subscribersCollection;

    /**
     * Website collection array
     *
     * @var array
     */
    protected $_websites;

    protected $stockItem;

    protected $_collectionStock;
    protected $_collectionPrice;
    protected $_email;
    protected $sourceDataBySku;

    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var TimezoneInterface
     */
    protected $_timezoneInterface;

    public function __construct(
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockItem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Lof\ProductNotification\Model\ResourceModel\Stock\Collection $collectionStock,
        \Lof\ProductNotification\Model\ResourceModel\Price\Collection $collectionPrice,
        \Lof\ProductNotification\Model\Email $email,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollectionFactory,
        \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
        TimezoneInterface $timezoneInterface,
        DateTime $dateTime
    )
    {
        $this->_email = $email;
        $this->_collectionPrice = $collectionPrice;
        $this->_collectionStock = $collectionStock;
        $this->_catalogData     = $catalogData;
        $this->stockItem = $stockItem;
        $this->_dateFactory     = $dateFactory;
        $this->_scopeConfig     = $scopeConfig;
        $this->_storeManager      = $storeManager;
        $this->catalogSession   = $catalogSession;
        $this->customerRepository = $customerRepository;
        $this->sourceDataBySku = $sourceDataBySku;
        $this->_subscribersCollection = $subscriberCollectionFactory->create();
        $this->_dateTime = $dateTime;
        $this->_timezoneInterface = $timezoneInterface;
    }

    public function getTimezoneDateTime($dateTime = "today"){
        if($dateTime === "today" || !$dateTime){
            $dateTime = $this->_dateTime->gmtDate();
        }
        
        $today = $this->_timezoneInterface
            ->date(
                new \DateTime($dateTime)
            )->format('Y-m-d H:i:s');
        return $today;
    }

    public function getSourceStocks($_product_sku){
        return $this->sourceDataBySku->execute($_product_sku);
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product = $observer->getProduct();
        
        $stock = $this->stockItem->getStockItem($_product->getId());
        $email = $this->_email;
        if($this->getConfigEmailNewProduct() == 1){
            if($_product->isObjectNew()){
                $cat_ids = $this->getConfigCatIds();
                $is_notify = false;
                if($cat_ids) {
                    $categoryIds = $_product->getCategoryIds();
                    if($categoryIds){
                        foreach($categoryIds as $cat_id) {
                            if(in_array($cat_id, $cat_ids)){
                                $is_notify = true;
                                break;
                            }
                        }
                    }
                } else {
                    $is_notify = true;
                }
                if($is_notify) {
                    $this->_processNewProduct($email, $_product);
                }
            }
        }
        if($this->getConfigEmailPrice() == 1){
            $modelPrice = $this->_collectionPrice->addFieldToFilter('product_id',$_product->getId());
            $old_special_price = $_product->getOrigData("special_price"); //$this->catalogSession->getOldSpecialPrice();
            $old_special_from_date = $_product->getOrigData("special_from_date"); //$this->catalogSession->getOldSpecialFromDate();
            $old_special_to_date = $_product->getOrigData("special_to_date"); //$this->catalogSession->getOldSpecialToDate();
            $special_price = $_product->getSpecialPrice();
            $special_from_date = $_product->getSpecialFromDate();
            $special_to_date = $_product->getSpecialToDate();
            $is_send_price_drops = false;

            $_product_price = (float)$_product->getData('price');
            if($special_price){
                if($old_special_price != $special_price || $old_special_from_date != $special_from_date || $old_special_to_date !== $special_to_date){
                    $today = $this->getTimezoneDateTime();
                    $today = strtotime($today);
                    if(!$special_from_date && !$special_to_date){
                        $is_send_price_drops = true;
                    }else {
                        if($special_from_date){
                            $special_from_date = $this->getTimezoneDateTime($special_from_date);
                            $special_from_date = strtotime($special_from_date);
                            if($special_from_date <= $today){
                                $is_send_price_drops = true;
                            }
                        }
                        if($special_to_date){
                            $special_to_date = $this->getTimezoneDateTime($special_to_date);
                            $special_to_date = strtotime($special_to_date);
                            if($special_to_date > $today){
                                $is_send_price_drops = true;
                            }
                        }
                    }
                }
                if($is_send_price_drops){
                    $_product_price = $special_price;
                }
            }
            if(!$is_send_price_drops) {
                if ((float)$this->catalogSession->getOldPrice() > (float)$_product->getData('price')) {   
                    $is_send_price_drops = true;
                }
            }
            if($is_send_price_drops && $modelPrice->count()){
                $this->_processPrice($email, $modelPrice, $_product, $_product_price);
            }
        }
        if($this->getConfigEmailStock() == 1) {
            $source_stocks = $this->getSourceStocks($_product->getSku());
            $qty = 0;
            $is_in_stock = false;
            if(!$source_stocks){
                $modelStock =  $this->_collectionStock->addFieldToFilter('product_id',$_product->getId());
                $qty = $stock->getQty();
                $is_in_stock = $stock->getIsInStock();
                $old_qty = $this->catalogSession->getOldProductQty();
                if (!$old_qty && $qty >= 1 && $is_in_stock == true) {
                    if ($modelStock->count()) {
                        $this->_processStock($email, $modelStock, $_product);
                    }
                }
            }
        }
        $this->catalogSession->unsOldQty();
        $this->catalogSession->unsOldPrice();
    }

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getConfigEmailStock() {

        $stockMail = $this->_scopeConfig->getValue(
            'productnotification/productalert/enable_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $stockMail;
    }

    public function getConfigEmailPrice() {
        $priceMail = $this->_scopeConfig->getValue(
            'productnotification/productalert/enable_price',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $priceMail;
    }

    public function getConfigEmailNewProduct() {
        $newproductMail = $this->_scopeConfig->getValue(
            'productnotification/productalert/allow_new_product',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $newproductMail;
    }

    public function getConfigCatIds() {
        $cat_ids = $this->_scopeConfig->getValue(
            'productnotification/productalert/cat_ids',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $cat_ids = trim($cat_ids);
        if($cat_ids) {
            $cat_ids_array = explode(",",$cat_ids);
            $tmp = [];
            foreach($cat_ids_array as $cat_id) {
                $cat_id = trim($cat_id);
                $tmp[] = (int)$cat_id;
            }
            $cat_ids = implode(",",$cat_ids);
        }
        return $cat_ids;
    }

    protected function _processStock(\Lof\ProductNotification\Model\Email $email,\Lof\ProductNotification\Model\ResourceModel\Stock\Collection $collectionStock,$_product)
    {
        $email->setType('stock');
        //$stock = $this->stockItem->getStockItem($_product->getId());

        foreach ($this->_getWebsites() as $website) {
            /* @var $website \Magento\Store\Model\Website */

            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }
            if (!$this->_scopeConfig->getValue(
                self::XML_PATH_STOCK_ALLOW,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $website->getDefaultGroup()->getDefaultStore()->getId()
            )) {
                continue;
            }

            if (!$this->_scopeConfig->getValue(self::XML_PATH_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $website->getDefaultGroup()->getDefaultStore()->getId())) {
                continue;
            }

            $previousEmail = null;
            $email->setWebsite($website);

            foreach ($collectionStock as $alert) {
                $email->setAlert($alert);
                $email->setToken($alert['token']);
                try {

                    $previousEmail = $alert->getSubscriberEmail();

                    $email->setSubscriberEmail($alert->getSubscriberEmail());
                    $email->setSubscriberName($alert['subscriber_name']);
                    if ($alert['customer_id'] >0) {
                        $customer = $this->customerRepository->getById($alert['customer_id']);
                        $previousEmail = $customer->getEmail();
                        $email->setCustomerData($customer);

                        $customerName = $customer->getFirstName();
                        if ($customer->getMiddleName()) {
                            $customerName .= ' ' . $customer->getMiddleName();
                        }
                        $customerName .= ' ' . $customer->getLastName();
                        $email->setSubscriberEmail($customer->getEmail());
                        $email->setSubscriberName($customerName);
                    }
                    //if ($stock->getQty() >= 1) {

                    $email->addStockProduct($_product);
                    $alert->setSendDate($this->_dateFactory->create()->gmtDate());
                    $alert->setSendCount($alert['send_count'] + 1);
                    $alert->setStatus(1);
                    $alert->save();
                    //}


                    if ($previousEmail) {
                        $email->send();
                    }
                    $email->clean();
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
        }

        return $this;
    }

    /**
     * Process price emails
     *
     * @param \Lof\ProductNotification\Model\Email $email
     * @param \Lof\ProductNotification\Model\ResourceModel\Price\Collection $collectionPriceLof
     * @param $_product
     * @param int $_product_price
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _processPrice(\Lof\ProductNotification\Model\Email $email,\Lof\ProductNotification\Model\ResourceModel\Price\Collection $collectionPriceLof, $_product, $_product_price = 0)
    {
        $email->setType('price');
        if(!$_product_price){
            $_product_price = $_product->getFinalPrice();
        }

        foreach ($this->_getWebsites() as $website) {

            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }
            if (!$this->_scopeConfig->getValue(
                self::XML_PATH_PRICE_ALLOW,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $website->getDefaultGroup()->getDefaultStore()->getId()
            )
            ) {
                continue;
            }

            if (!$this->_scopeConfig->getValue(
                self::XML_PATH_ENABLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $website->getDefaultGroup()->getDefaultStore()->getId()
            )
            ) {
                continue;
            }
            $previousEmail = null;
            $email->setWebsite($website);
            foreach ($collectionPriceLof as $alert) {
                try {
                    $previousEmail = $alert->getSubscriberEmail();
                    $email->setSubscriberEmail($alert->getSubscriberEmail());
                    $email->setSubscriberName($alert->getSubscriberName());

                    if ($alert->getCustomerId()) {
                        $customer = $this->customerRepository->getById($alert->getCustomerId());
                        $previousEmail = $customer->getEmail();
                        $email->setCustomerData($customer);

                        $customerName = $customer->getFirstName();
                        if ($customer->getMiddleName()) {
                            $customerName .= ' ' . $customer->getMiddleName();
                        }
                        $customerName .= ' ' . $customer->getLastName();
                        $email->setSubscriberEmail($customer->getEmail());
                        $email->setSubscriberName($customerName);
                    }

                    $email->setAlert($alert);
                    if ($alert->getPrice() != $_product_price) {
                        $_product->setFinalPrice($this->_catalogData->getTaxPrice($_product, $_product_price));
                        $_product->setPrice($this->_catalogData->getTaxPrice($_product, $_product->getPrice()));
                        $email->addPriceProduct($_product);

                        $alert->setPrice($_product_price);
                        $alert->setLastSendDate($this->_dateFactory->create()->gmtDate());
                        $alert->setSendCount($alert->getSendCount() + 1);
                        $alert->setStatus(1);
                        $alert->save();
                        if ($previousEmail) {
                            $email->send();
                        }
                    }
                    $email->clean();
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
        }
        return $this;
    }

    /**
     * Process price emails
     *
     * @param \Lof\ProductNotification\Model\Email $email
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _processNewProduct(\Lof\ProductNotification\Model\Email $email,$_product)
    {
        $email->setType('new_product');
        foreach ($this->_getWebsites() as $website) {

            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }
            if (!$this->_scopeConfig->getValue(
                self::XML_PATH_PRICE_ALLOW,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $website->getDefaultGroup()->getDefaultStore()->getId()
            )
            ) {
                continue;
            }

            if (!$this->_scopeConfig->getValue(
                self::XML_PATH_ENABLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $website->getDefaultGroup()->getDefaultStore()->getId()
            )
            ) {
                continue;
            }

            $email->setWebsite($website);
            if ($this->_subscribersCollection->getSize() == 0) {
                return $this;
            }

            $collection = $this->_subscribersCollection->useOnlyUnsent()->showCustomerInfo()->load();
            foreach ($collection as $alert) {
                try {
                    if($subscriber_email = $alert->getSubscriberEmail()) {
                        $email->setSubscriberEmail($subscriber_email);
                        $email->setSubscriberName($alert->getSubscriberFullName());
                        $email->setAlert($alert);

                        $productPrice = $_product->getFinalPrice();
                        $_product->setFinalPrice($this->_catalogData->getTaxPrice($_product, $productPrice));
                        $_product->setPrice($this->_catalogData->getTaxPrice($_product, $_product->getPrice()));
                        $email->addNewProduct($_product);
                        $email->send();
                        $email->clean();
                    }
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
        }
        return $this;
    }

    protected function _getWebsites()
    {
        if ($this->_websites === null) {
            try {
                $this->_websites = $this->_storeManager->getWebsites();
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
            }
        }
        return $this->_websites;
    }
}