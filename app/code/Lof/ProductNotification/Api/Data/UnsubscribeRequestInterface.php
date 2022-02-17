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

interface UnsubscribeRequestInterface
{

    const ID = 'id';
    const AID = 'aid';
    const EMAIL = 'email';
    const TOKEN = 'token';
    const WEBSITE_ID = 'website_id';
    const CUSTOMER_ID = 'customer_id';

    /**
     * Get id
     * @return int|null
     */
    public function getId();

    /**
     * Set id
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get aid
     * @return string|null
     */
    public function getAid();

    /**
     * Set aid
     * @param string $aid
     * @return $this
     */
    public function setAid($aid);

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
     * Get email
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     * @param string $token
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get website_id
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Set website_id
     * @param int $website_id
     * @return $this
     */
    public function setWebsiteId($website_id);

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
}
