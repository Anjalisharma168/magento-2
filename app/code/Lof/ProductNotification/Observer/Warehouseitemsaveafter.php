<?php

namespace Lof\ProductNotification\Observer;

use Magento\Framework\Event\ObserverInterface;

class Warehouseitemsaveafter implements ObserverInterface
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
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollectionFactory
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
        $this->_subscribersCollection = $subscriberCollectionFactory->create();
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_warehouseitem = $observer->getWarehouseItem();
        if($_warehouseitem) {
            $_product = $_warehouseitem->getProduct();
            $stock = $this->stockItem->getStockItem($_product->getId());
            $modelStock =  $this->_collectionStock->addFieldToFilter('product_id',$_product->getId());
            $modelPrice = $this->_collectionPrice->addFieldToFilter('product_id',$_product->getId());
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
                if ($this->catalogSession->getOldPrice() > $_product->getData('price')) {
                    if(!empty($modelPrice->getData())){
                        $this->_processPrice($email, $modelPrice, $_product);
                    }
                }
            }

            if($this->getConfigEmailStock() == 1) {
                if ($this->catalogSession->getOldQty() == 0 && $stock->getQty() >= 1 && $stock->getIsInStock() == true) {
                    if (!empty($modelStock->getData())) {
                        $this->_processStock($email, $modelStock, $_product);
                    }
                }
            }
            $this->catalogSession->unsOldQty();
            $this->catalogSession->unsOldPrice();
        }
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
        $stock = $this->stockItem->getStockItem($_product->getId());

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
                    if ($stock->getQty() >= 1) {

                        $email->addStockProduct($_product);
                        $alert->setSendDate($this->_dateFactory->create()->gmtDate());
                        $alert->setSendCount($alert['send_count'] + 1);
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
    protected function _processPrice(\Lof\ProductNotification\Model\Email $email,\Lof\ProductNotification\Model\ResourceModel\Price\Collection $collectionPriceLof,$_product)
    {
        $email->setType('price');
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

                    if ($alert->getPrice() != $_product->getFinalPrice()) {
                        $productPrice = $_product->getFinalPrice();
                        $_product->setFinalPrice($this->_catalogData->getTaxPrice($_product, $productPrice));
                        $_product->setPrice($this->_catalogData->getTaxPrice($_product, $_product->getPrice()));
                        $email->addPriceProduct($_product);

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