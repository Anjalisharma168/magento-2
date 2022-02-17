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

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Lof\ProductNotification\Api\Data\StockInterface;

class StockLog extends \Magento\Framework\Model\AbstractModel implements StockInterface
{
    /**#@-*/

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'productnotification_stock_log';

    /**
     * @var string
     */
    protected $_cacheTag = 'productnotification_stock_log';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'productnotification_stock_log';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Stock constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\StockLog|null $resource
     * @param ResourceModel\StockLog\Collection|null $resourceCollection
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\ProductNotification\Model\ResourceModel\StockLog $resource = null,
        \Lof\ProductNotification\Model\ResourceModel\StockLog\Collection $resourceCollection = null,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productFactory = $productFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\ProductNotification\Model\ResourceModel\StockLog');
    }

    public function getId()
    {
        return $this->getData(self::ALERT_STOCK_ID);
    }

    public function getProduct()
    {
        $productId = $this->getData('product_id');
        $product = $this->productFactory->create()->load($productId);
        return $product;
    }

    public function getParams()
    {
        return unserialize($this->getData('params'));
    }

    public function getAvailableProduct()
    {
        $params = $this->getParams();
        if (isset($params[Configurable::TYPE_CODE])) {
            $productId = $this->getData('product_id');
            $product = $this->productFactory->create()->load($params[Configurable::TYPE_CODE]);
            return $product;
        }
        return false;
    }

    /**
     * @param int $customerId
     * @param int $websiteId
     * @return $this
     */
    public function deleteCustomer($customerId, $websiteId = 0)
    {
        $this->getResource()->deleteCustomer($this, $customerId, $websiteId);
        return $this;
    }

    /**
     * @param int $customerId
     * @param int $websiteId
     * @return $this
     */
    public function deleteGuest($email, $websiteId = 0)
    {
        $this->getResource()->deleteGuest($this, $email, $websiteId);
        return $this;
    }

    /**
     * Get alert_stock_id
     * @return int|null
     */
    public function getAlertStockId(){
        return $this->getData(self::ALERT_STOCK_ID);
    }

    /**
     * Set alert_stock_id
     * @param int $alert_stock_id
     * @return $this
     */
    public function setAlertStockId($alert_stock_id){
        return $this->setData(self::ALERT_STOCK_ID, $alert_stock_id);
    }

    /**
     * Get customer_id
     * @return int|null
     */
    public function getCustomerId(){
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param int $customer_id
     * @return $this
     */
    public function setCustomerId($customer_id){
        return $this->setData(self::CUSTOMER_ID, $customer_id);
    }

    /**
     * Get product_id
     * @return int|null
     */
    public function getProductId(){
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Set product_id
     * @param int $product_id
     * @return $this
     */
    public function setProductId($product_id){
        return $this->setData(self::PRODUCT_ID, $product_id);
    }

    /**
     * Get parent_product_id
     * @return string|null
     */
    public function getParentProductId(){
        return $this->getData(self::PARENT_PRODUCT_ID);
    }

    /**
     * Set parent_product_id
     * @param string $parent_product_id
     * @return $this
     */
    public function setParentProductId($parent_product_id){
        return $this->setData(self::PARENT_PRODUCT_ID, $parent_product_id);
    }

    /**
     * Get super_attribute
     * @return string|null
     */
    public function getSuperAttribute(){
        return $this->getData(self::SUPER_ATTRIBUTE);
    }

    /**
     * Set super_attribute
     * @param string $super_attribute
     * @return $this
     */
    public function setSuperAttribute($super_attribute){
        return $this->setData(self::SUPER_ATTRIBUTE, $super_attribute);
    }

    /**
     * Get product_sku
     * @return string|null
     */
    public function getProductSku(){
        return $this->getData(self::PRODUCT_SKU);
    }

    /**
     * Set product_sku
     * @param string $product_sku
     * @return $this
     */
    public function setProductSku($product_sku){
        return $this->setData(self::PRODUCT_SKU, $product_sku);
    }

    /**
     * Get store_id
     * @return int|null
     */
    public function getStoreId(){
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set store_id
     * @param int $store_id
     * @return $this
     */
    public function setStoreId($store_id){
        return $this->setData(self::STORE_ID, $store_id);
    }

    /**
     * Get website_id
     * @return string|null
     */
    public function getWebsiteId(){
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * Set website_id
     * @param string $website_id
     * @return $this
     */
    public function setWebsiteId($website_id){
        return $this->setData(self::WEBSITE_ID, $website_id);
    }

    /**
     * Get add_date
     * @return string|null
     */
    public function getAddDate(){
        return $this->getData(self::ADD_DATE);
    }

    /**
     * Set add_date
     * @param string $add_date
     * @return $this
     */
    public function setAddDate($add_date){
        return $this->setData(self::ADD_DATE, $add_date);
    }

    /**
     * Get send_count
     * @return string|null
     */
    public function getSendCount(){
        return $this->getData(self::SEND_COUNT);
    }

    /**
     * Set send_count
     * @param string $send_count
     * @return $this
     */
    public function setSendCount($send_count){
        return $this->setData(self::SEND_COUNT, $send_count);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus(){
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return $this
     */
    public function setStatus($status){
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get subscriber_email
     * @return string|null
     */
    public function getSubscriberEmail(){
        return $this->getData(self::SUBSCRIBER_EMAIL);
    }

    /**
     * Set subscriber_email
     * @param string $subscriber_email
     * @return $this
     */
    public function setSubscriberEmail($subscriber_email){
        return $this->setData(self::SUBSCRIBER_EMAIL, $subscriber_email);
    }

    /**
     * Get subscriber_name
     * @return string|null
     */
    public function getSubscriberName(){
        return $this->getData(self::SUBSCRIBER_NAME);
    }

    /**
     * Set subscriber_name
     * @param string $subscriber_name
     * @return $this
     */
    public function setSubscriberName($subscriber_name){
        return $this->setData(self::SUBSCRIBER_NAME, $subscriber_name);
    }

    /**
     * Get token
     * @return string|null
     */
    public function getToken(){
        return $this->getData(self::TOKEN);
    }

    /**
     * Set token
     * @param string $token
     * @return $this
     */
    public function setToken($token){
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * Get message
     * @return string|null
     */
    public function getMessage(){
        return $this->getData(self::MESSAGE);
    }

    /**
     * Set message
     * @param string $message
     * @return $this
     */
    public function setMessage($message){
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * Get send_date
     * @return string|null
     */
    public function getSendDate(){
        return $this->getData(self::SEND_DATE);
    }

    /**
     * Set send_date
     * @param string $send_date
     * @return $this
     */
    public function setSendDate($send_date){
        return $this->setData(self::SEND_DATE, $send_date);
    }


    /**
     * Get productImage
     * @return string|null
     */
    public function getProductImage() {
        return $this->getProductImageUrl();
    }

    /**
     * Get productName
     * @return string|null
     */
    public function getProductName() {
        $product = $this->getProductData();
        return $product ? $product->getName() : "";
    }

    /**
     * Get productUrl
     * @return string|null
     */
    public function getProductUrl() {
        $product = $this->getProductData();
        return $product ? $product->getProductUrl() : "";
    }

    public function getProductData()
    {
        $productId = $this->getData('product_id');
        try
        {
            $product = $this->productFactory->create()->load($productId);
        }
        catch (NoSuchEntityException $e)
        {
            return false;
        }
        return $product;
    }

    public function getProductImageUrl()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $product = $this->getProductData() ?: 'Data not found';
        $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        return $productImageUrl;
    }
}
