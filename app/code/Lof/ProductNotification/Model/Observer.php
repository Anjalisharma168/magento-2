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

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

class Observer
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
     * Allow stock alert
     *
     */
    const XML_PATH_STOCK_ALLOW = 'productnotification/productalert/allow_stock';

    const XML_PATH_ENABLE = 'productnotification/general_settings/enable';
    const XML_PATH_SEND_MAIL_ONE_TIME = 'productnotification/productalert/send_email_one_time';

    /**
     * Website collection array
     *
     * @var array
     */
    protected $_websites;

    /**
     * Warning (exception) errors array
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

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
     * @var \Lof\ProductNotification\Model\ResourceModel\Price\CollectionFactory
     */
    protected $_priceColFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $_dateFactory;

    /**
     * @var \Lof\ProductNotification\Model\ResourceModel\Stock\CollectionFactory
     */
    protected $_stockColFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Lof\ProductNotification\Model\EmailFactory
     */
    protected $_emailFactory;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var TimezoneInterface
     */
    protected $_timezoneInterface;

    /**
     * Observer constructor.
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ResourceModel\Price\CollectionFactory $priceColFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param ResourceModel\Stock\CollectionFactory $stockColFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param EmailFactory $emailFactory
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param TimezoneInterface $timezoneInterface
     * @param DateTime $dateTime
     */
    public function __construct(
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Lof\ProductNotification\Model\ResourceModel\Price\CollectionFactory $priceColFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Lof\ProductNotification\Model\ResourceModel\Stock\CollectionFactory $stockColFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Lof\ProductNotification\Model\EmailFactory $emailFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        TimezoneInterface $timezoneInterface,
        DateTime $dateTime
        ) {
        $this->_catalogData       = $catalogData;
        $this->_scopeConfig       = $scopeConfig;
        $this->_storeManager      = $storeManager;
        $this->_priceColFactory   = $priceColFactory;
        $this->_customerRepository = $customerRepository;
        $this->_productRepository  = $productRepository;
        $this->_dateFactory       = $dateFactory;
        $this->_stockColFactory   = $stockColFactory;
        $this->_transportBuilder  = $transportBuilder;
        $this->_emailFactory      = $emailFactory;
        $this->_inlineTranslation  = $inlineTranslation;
        $this->_dateTime = $dateTime;
        $this->_timezoneInterface = $timezoneInterface;
    }

    /**
     * Retrieve website collection array
     *
     * @return mixed
     */
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

    protected function checkPriceIsDrops($alert_price, $product) {
        $product_final_price = (float)$product->getFinalPrice();
        $special_price = $product->getSpecialPrice();
        $special_from_date = $product->getSpecialFromDate();
        $special_to_date = $product->getSpecialToDate();
        $is_send_price_drops = false;
        if($special_price && $alert_price != $special_price){
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
        if(!$is_send_price_drops) {
            if ((float)$alert_price != (float)$product_final_price) {   
                $is_send_price_drops = true;
            }
        }
        return $is_send_price_drops;
    }

    /**
     * Process price emails
     *
     * @param \Lof\ProductNotification\Model\Email $email
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _processPrice(\Lof\ProductNotification\Model\Email $email)
    {
        $email->setType('price');
        foreach ($this->_getWebsites() as $website) {
            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }
            $enabled = $this->_scopeConfig->getValue(self::XML_PATH_ENABLE,ScopeInterface::SCOPE_STORE,$website->getDefaultGroup()->getDefaultStore()->getId());
            $allow_send_price_notify = $this->_scopeConfig->getValue(self::XML_PATH_PRICE_ALLOW,ScopeInterface::SCOPE_STORE,$website->getDefaultGroup()->getDefaultStore()->getId());
            $send_email_one_time = $this->_scopeConfig->getValue(self::XML_PATH_SEND_MAIL_ONE_TIME, ScopeInterface::SCOPE_STORE, $website->getDefaultGroup()->getDefaultStore()->getId());
            $send_email_one_time = $send_email_one_time?(int)$send_email_one_time:0;

            if (!$enabled) {
                continue;
            }
            if (!$allow_send_price_notify) {
                continue;
            }
            $collection = $this->_priceColFactory->create()->addWebsiteFilter($website->getId())->setCustomerOrder();
            if($send_email_one_time){
                $collection->addFieldToFilter("status", 0);
            }

            $previousEmail = null;
            $email->setWebsite($website);
            if($collection->count()){
                foreach ($collection as $alert) {
                    try {
                        $previousEmail = $alert->getSubscriberEmail();
                        $email->setSubscriberEmail($alert->getSubscriberEmail());
                        $email->setSubscriberName($alert->getSubscriberName());

                        if ($alert->getCustomerId()) {
                            $customer = $this->_customerRepository->getById($alert->getCustomerId());
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

                        $product = $this->_productRepository->getById($alert->getProductId(),false,$website->getDefaultStore()->getId());
                        $email->setAlert($alert);
                        
                        $is_send_price_drops = $this->checkPriceIsDrops($alert->getPrice(), $product);
                        if ($is_send_price_drops) {
                            $productPrice = $product->getFinalPrice();
                            $product->setFinalPrice($this->_catalogData->getTaxPrice($product, $productPrice));
                            $product->setPrice($this->_catalogData->getTaxPrice($product, $product->getPrice()));
                            $email->addPriceProduct($product);

                            $alert->setPrice($productPrice);
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
        }
        return $this;
    }

    /**
     * Process stock emails
     *
     * @param \Lof\ProductNotification\Model\Email $email
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _processStock(\Lof\ProductNotification\Model\Email $email)
    {

        $email->setType('stock');
        
        foreach ($this->_getWebsites() as $website) {
            /* @var $website \Magento\Store\Model\Website */
            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }
            $enabled = $this->_scopeConfig->getValue(self::XML_PATH_ENABLE, ScopeInterface::SCOPE_STORE, $website->getDefaultGroup()->getDefaultStore()->getId());
            $allow_send_stock_notify = $this->_scopeConfig->getValue(self::XML_PATH_STOCK_ALLOW,ScopeInterface::SCOPE_STORE,$website->getDefaultGroup()->getDefaultStore()->getId());
            $send_email_one_time = $this->_scopeConfig->getValue(self::XML_PATH_SEND_MAIL_ONE_TIME, ScopeInterface::SCOPE_STORE, $website->getDefaultGroup()->getDefaultStore()->getId());
            $send_email_one_time = $send_email_one_time?(int)$send_email_one_time:0;
            if (!$enabled) {
                continue;
            }
            if (!$allow_send_stock_notify) {
                continue;
            }

            $collection = $this->_stockColFactory->create()->addWebsiteFilter($website->getId())->setCustomerOrder();
            if($send_email_one_time){
                $collection->addFieldToFilter("status", 0);
            }

            $previousEmail = null;
            $email->setWebsite($website);

            if($collection->count()){
                foreach ($collection as $alert) {
                    $email->setAlert($alert);
                    $email->setToken($alert->getToken());
                    try {
                        $previousEmail = $alert->getSubscriberEmail();
                        $email->setSubscriberEmail($alert->getSubscriberEmail());
                        $email->setSubscriberName($alert->getSubscriberName());
                        if ($alert->getCustomerId()) {
                            $customer = $this->_customerRepository->getById($alert->getCustomerId());
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

                        $product = $this->_productRepository->getById($alert->getProductId(),false, $website->getDefaultStore()->getId());
                        $product_stock_status = $product->getQuantityAndStockStatus();
                        if ($product_stock_status && isset($product_stock_status['is_in_stock']) && $product_stock_status['is_in_stock']) {
                            $email->addStockProduct($product);
                            $alert->setSendDate($this->_dateFactory->create()->gmtDate());
                            $alert->setSendCount($alert->getSendCount() + 1);
                            $alert->setStatus(1);
                            $alert->save();
                        }

                        if ($previousEmail) {
                            $email->send();
                        }
                        $email->clean();
                    } catch (\Exception $e) {
                        $this->_errors[] = $e->getMessage();
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Send email to administrator if error
     *
     * @return $this
     */
    protected function _sendErrorEmail()
    {
        if (count($this->_errors)) {
            if (!$this->_scopeConfig->getValue(self::XML_PATH_ERROR_TEMPLATE,ScopeInterface::SCOPE_STORE
                )) {
                return $this;
            }
            $this->_inlineTranslation->suspend();
            $email_identifier = $this->_scopeConfig->getValue(self::XML_PATH_ERROR_TEMPLATE,ScopeInterface::SCOPE_STORE);
            $from_email_address = $this->_scopeConfig->getValue(self::XML_PATH_ERROR_IDENTITY,ScopeInterface::SCOPE_STORE);
            $to_email_address = $this->_scopeConfig->getValue(self::XML_PATH_ERROR_RECIPIENT,ScopeInterface::SCOPE_STORE);
            if($to_email_address){
                $transport = $this->_transportBuilder->setTemplateIdentifier($email_identifier)->setTemplateOptions(
                    [
                        'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    ]
                )->setTemplateVars(
                    ['warnings' => implode("\n", $this->_errors)]
                )->setFrom(
                    $from_email_address
                )->addTo(
                    $to_email_address
                )->getTransport();

                $transport->sendMessage();
                $this->_inlineTranslation->resume();
            }
            $this->_errors = [];
        }
        return $this;
    }

    /**
     * Run process send product alerts
     *
     * @return $this
     */
    public function process()
    {
        /* @var $email \Lof\ProductNotification\Model\Email */
        $email = $this->_emailFactory->create();
        $this->_processPrice($email);
        $this->_processStock($email);
        $this->_sendErrorEmail();
        return $this;
    }
}
