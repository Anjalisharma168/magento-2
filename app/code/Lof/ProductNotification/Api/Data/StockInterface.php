<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_ProductNotification
 * @copyright  Copyright (c) 2020 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\ProductNotification\Api\Data;

interface StockInterface
{

    const ALERT_STOCK_ID = 'alert_stock_id';
    const CUSTOMER_ID = 'customer_id';
    const PRODUCT_ID = 'product_id';
    const PRODUCT_SKU = 'product_sku';
    const PARENT_PRODUCT_ID = 'parent_product_id';
    const WEBSITE_ID = 'website_id';
    const ADD_DATE = 'add_date';
    const SEND_COUNT = 'send_count';
    const STATUS = 'status';
    const SUBSCRIBER_EMAIL = 'subscriber_email';
    const SUBSCRIBER_NAME = 'subscriber_name';
    const SUPER_ATTRIBUTE = 'super_attribute';
    const TOKEN = 'token';
    const MESSAGE = 'message';
    const PARAMS = 'params';
    const STORE_ID = 'store_id';
    const SEND_DATE = 'send_date';

    /**
     * Get alert_stock_id
     * @return int|null
     */
    public function getAlertStockId();

    /**
     * Set alert_stock_id
     * @param int $alert_stock_id
     * @return $this
     */
    public function setAlertStockId($alert_stock_id);

    /**
     * Get customer_id
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param int $customer_id
     * @return $this
     */
    public function setCustomerId($customer_id);

    /**
     * Get product_id
     * @return int|null
     */
    public function getProductId();

    /**
     * Set product_id
     * @param int $product_id
     * @return $this
     */
    public function setProductId($product_id);

    /**
     * Get store_id
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param int $store_id
     * @return $this
     */
    public function setStoreId($store_id);

    /**
     * Get parent_product_id
     * @return string|null
     */
    public function getParentProductId();

    /**
     * Set parent_product_id
     * @param string $parent_product_id
     * @return $this
     */
    public function setParentProductId($parent_product_id);

    /**
     * Get product_sku
     * @return string|null
     */
    public function getProductSku();

    /**
     * Set product_sku
     * @param string $product_sku
     * @return $this
     */
    public function setProductSku($product_sku);

    /**
     * Get website_id
     * @return string|null
     */
    public function getWebsiteId();

    /**
     * Set website_id
     * @param string $website_id
     * @return $this
     */
    public function setWebsiteId($website_id);

    /**
     * Get add_date
     * @return string|null
     */
    public function getAddDate();

    /**
     * Set add_date
     * @param string $add_date
     * @return $this
     */
    public function setAddDate($add_date);

    /**
     * Get send_date
     * @return string|null
     */
    public function getSendDate();

    /**
     * Set send_date
     * @param string $send_date
     * @return $this
     */
    public function setSendDate($send_date);

    /**
     * Get send_count
     * @return string|null
     */
    public function getSendCount();

    /**
     * Set send_count
     * @param string $send_count
     * @return $this
     */
    public function setSendCount($send_count);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get subscriber_email
     * @return string|null
     */
    public function getSubscriberEmail();

    /**
     * Set subscriber_email
     * @param string $subscriber_email
     * @return $this
     */
    public function setSubscriberEmail($subscriber_email);

    /**
     * Get subscriber_name
     * @return string|null
     */
    public function getSubscriberName();

    /**
     * Set subscriber_name
     * @param string $subscriber_name
     * @return $this
     */
    public function setSubscriberName($subscriber_name);

    /**
     * Get token
     * @return string|null
     */
    public function getToken();

    /**
     * Set token
     * @param string $token
     * @return $this
     */
    public function setToken($token);

    /**
     * Get message
     * @return string|null
     */
    public function getMessage();

    /**
     * Set message
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Get super_attribute
     * @return string|null
     */
    public function getSuperAttribute();

    /**
     * Set super_attribute
     * @param string $super_attribute
     * @return $this
     */
    public function setSuperAttribute($super_attribute);

    /**
     * Get productImage
     * @return string|null
     */
    public function getProductImage();

    /**
     * Get productName
     * @return string|null
     */
    public function getProductName();

    /**
     * Get productUrl
     * @return string|null
     */
    public function getProductUrl();
}
