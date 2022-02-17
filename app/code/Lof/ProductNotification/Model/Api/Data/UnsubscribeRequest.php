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

namespace Lof\ProductNotification\Model\Api\Data;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Lof\ProductNotification\Api\Data\UnsubscribeRequestInterface;

class UnsubscribeRequest extends \Magento\Framework\Api\AbstractExtensibleObject implements UnsubscribeRequestInterface
{
    /**
     * Get id
     * @return int|null
     */
    public function getId(){
        return $this->_get(self::ID);
    }

    /**
     * Set id
     * @param int $id
     * @return $this
     */
    public function setId($id){
        return $this->setData(self::ID, $id);
    }

    /**
     * Get aid
     * @return string|null
     */
    public function getAid(){
        return $this->_get(self::AID);
    }

    /**
     * Set aid
     * @param string $aid
     * @return $this
     */
    public function setAid($aid){
        return $this->setData(self::AID, $aid);
    }

    /**
     * Get token
     * @return string|null
     */
    public function getToken(){
        return $this->_get(self::TOKEN);
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
     * Get website_id
     * @return int|null
     */
    public function getWebsiteId(){
        return $this->_get(self::WEBSITE_ID);
    }

    /**
     * Set website_id
     * @param int $website_id
     * @return $this
     */
    public function setWebsiteId($website_id){
        return $this->setData(self::WEBSITE_ID, $website_id);
    }

    /**
     * Get email
     * @return string|null
     */
    public function getEmail(){
        return $this->_get(self::EMAIL); 
    }

    /**
     * Set email
     * @param string $token
     * @return $this
     */
    public function setEmail($email){
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get customer_id
     * @return int|null
     */
    public function getCustomerId(){
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param int $customer_id
     * @return $this
     */
    public function setCustomerId($customer_id){
        return $this->setData(self::CUSTOMER_ID, $customer_id);
    }
}
