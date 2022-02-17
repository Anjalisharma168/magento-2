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

/** copy code from /vendor/magento/module-inventory-catalog-admin-ui/Observer/ProcessSourceItemsObserver.php **/

namespace Lof\ProductNotification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\InputException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryCatalogApi\Model\SourceItemsProcessorInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

class CheckProductStockAfterSave implements ObserverInterface
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
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

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
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        SourceItemsProcessorInterface $sourceItemsProcessor,
        IsSingleSourceModeInterface $isSingleSourceMode,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SourceItemRepositoryInterface $sourceItemRepository
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
        //copy code from /vendor/magento/module-inventory-catalog-admin-ui/Observer/ProcessSourceItemsObserver.php
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->sourceItemsProcessor = $sourceItemsProcessor;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->sourceItemRepository = $sourceItemRepository;
        //end copy
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product = $observer->getEvent()->getProduct();
        if ($this->isSourceItemManagementAllowedForProductType->execute($_product->getTypeId()) === false) {
            return;
        }
        $email = $this->_email;
        if($this->getConfigEmailStock() == 1) {
            $modelStock =  $this->_collectionStock->addFieldToFilter('product_id',$_product->getId());
            
            if(!$modelStock->count()) {
                return;
            }
            /** @var Save $controller */
            $controller = $observer->getEvent()->getController();
            $productData = $controller->getRequest()->getParam('product', []);
            $singleSourceData = $productData['quantity_and_stock_status'] ?? [];
            
            $source_stocks = [];
            if (!$this->isSingleSourceMode->execute()) {
                $sources = $controller->getRequest()->getParam('sources', []);
                $source_stocks =
                    isset($sources['assigned_sources'])
                    && is_array($sources['assigned_sources'])
                        ? $this->prepareAssignedSources($sources['assigned_sources'])
                        : [];
            } elseif (!empty($singleSourceData)) {
                /** @var StockItemInterface $stockItem */
                $stockItem = $product->getExtensionAttributes()->getStockItem();
                $qty = $singleSourceData['qty'] ?? (empty($stockItem) ? 0 : $stockItem->getQty());
                $isInStock = $singleSourceData['is_in_stock'] ?? (empty($stockItem) ? 1 : (int)$stockItem->getIsInStock());
                $defaultSourceData = [
                    SourceItemInterface::SKU => $productData['sku'],
                    SourceItemInterface::SOURCE_CODE => $this->defaultSourceProvider->getCode(),
                    SourceItemInterface::QUANTITY => $qty,
                    SourceItemInterface::STATUS => $isInStock,
                ];
                $sourceItems = $this->getSourceItemsWithoutDefault($productData['sku']);
                $sourceItems[] = $defaultSourceData;
                $source_stocks = $sourceItems;
            }
            if($source_stocks){
                $qty = 0;
                $is_in_stock = false;
                $stock_status = 0;
                foreach($source_stocks as $_source){
                    if($_source["status"] && $_source["source_status"]){
                        $stock_status = 1;
                        $qty += (int)$_source["quantity"];
                    }
                }
                if($stock_status){
                    $is_in_stock = true;
                }
                $oldQty = $this->catalogSession->getOldProductQty();
                if (!$oldQty && $qty >= 1 && $is_in_stock == true) {
                    $this->_processStock($email, $modelStock, $_product);
                    $this->catalogSession->setOldQty($qty);
                }
            }
        }else {
            return;
        }
    }

    /**
     * Get Source Items Data without Default Source by SKU
     *
     * @param string $sku
     * @return array
     */
    private function getSourceItemsWithoutDefault(string $sku): array
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->addFilter(SourceItemInterface::SOURCE_CODE, $this->defaultSourceProvider->getCode(), 'neq')
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        $sourceItemData = [];
        if ($sourceItems) {
            foreach ($sourceItems as $sourceItem) {
                $sourceItemData[] = [
                    SourceItemInterface::SKU => $sourceItem->getSku(),
                    SourceItemInterface::SOURCE_CODE => $sourceItem->getSourceCode(),
                    SourceItemInterface::QUANTITY => $sourceItem->getQuantity(),
                    SourceItemInterface::STATUS => $sourceItem->getStatus(),
                ];
            }
        }
        return $sourceItemData;
    }

    /**
     * Convert built-in UI component property qty into quantity and source_status into status
     *
     * @param array $assignedSources
     * @return array
     */
    private function prepareAssignedSources(array $assignedSources): array
    {
        foreach ($assignedSources as $key => $source) {
            if (!key_exists('quantity', $source) && isset($source['qty'])) {
                $source['quantity'] = (int) $source['qty'];
                $assignedSources[$key] = $source;
            }
            if (!key_exists('status', $source) && isset($source['source_status'])) {
                $source['status'] = (int) $source['source_status'];
                $assignedSources[$key] = $source;
            }
        }
        return $assignedSources;
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