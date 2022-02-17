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

interface PriceInterface
{

    const ALERT_PRICE_ID = 'alert_price_id';
    const CUSTOMER_ID = 'customer_id';
    const PRODUCT_ID = 'product_id';
    const PRICE = 'price';
    const WEBSITE_ID = 'website_id';
    const ADD_DATE = 'add_date';
    const SEND_COUNT = 'send_count';
    const STATUS = 'status';
    const SUBSCRIBER_EMAIL = 'subscriber_email';
    const SUBSCRIBER_NAME = 'subscriber_name';
    const TOKEN = 'token';
    const MESSAGE = 'message';
    const PRODUCT_SKU = 'product_sku';
    const CHILD_PRODUCT_ID = 'child_product_id';
    const LAST_SEND_DATE = 'last_send_date';
    const STORE_ID = 'store_id';


    /**
     * Get alert_price_id
     * @return int|null
     */
    public function getAlertPriceId();

    /**
     * Set alert_price_id
     * @param int $alert_price_id
     * @return $this
     */
    public function setAlertPriceId($alert_price_id);

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
     * Get last_send_date
     * @return int|null
     */
    public function getLastSendDate();

    /**
     * Set last_send_date
     * @param int $last_send_date
     * @return $this
     */
    public function setLastSendDate($last_send_date);

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
     * Get price
     * @return string|null
     */
    public function getPrice();

    /**
     * Set price
     * @param string $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get child_product_id
     * @return string|null
     */
    public function getChildProductId();

    /**
     * Set child_product_id
     * @param string $child_product_id
     * @return $this
     */
    public function setChildProductId($child_product_id);

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
}
