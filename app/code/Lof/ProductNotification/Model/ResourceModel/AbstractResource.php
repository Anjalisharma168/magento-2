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

namespace Lof\ProductNotification\Model\ResourceModel;

abstract class AbstractResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Retrieve alert row by object parameters
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return mixed|false
     */
    protected function _getAlertRow(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();
        if ($object->getProductId()) {
            if ($object->getCustomerId()) {
                $select = $connection->select()->from(
                    $this->getMainTable()
                )->where(
                    'customer_id = :customer_id'
                )->where(
                    'product_id  = :product_id'
                )->where(
                    'website_id  = :website_id'
                );
                $bind = [
                    ':customer_id' => $object->getCustomerId(),
                    ':product_id'  => $object->getProductId(),
                    ':website_id'  => $object->getWebsiteId()
                ];
                return $connection->fetchRow($select, $bind);
            } else {
                $select = $connection->select()->from(
                    $this->getMainTable()
                )->where(
                    'subscriber_email = :subscriber_email'
                )->where(
                    'product_id  = :product_id'
                )->where(
                    'website_id  = :website_id'
                );
                $bind = [
                    ':subscriber_email' => $object->getSubscriberEmail(),
                    ':product_id'       => $object->getProductId(),
                    ':website_id'       => $object->getWebsiteId()
                ];
                return $connection->fetchRow($select, $bind);
            }
        }
        return false;
    }

    /**
     * Load object data by parameters
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function loadByParam(\Magento\Framework\Model\AbstractModel $object)
    {
        $row = $this->_getAlertRow($object);
        if ($row) {
            $object->setData($row);
        }
        return $this;
    }

    /**
     * Delete all customer alerts on website
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param int $customerId
     * @param int $websiteId
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function deleteCustomer(\Magento\Framework\Model\AbstractModel $object, $customerId, $websiteId = null)
    {
        $connection = $this->getConnection();
        $where = [];
        $where[] = $connection->quoteInto('customer_id=?', $customerId);
        if ($websiteId) {
            $where[] = $connection->quoteInto('website_id=?', $websiteId);
        }
        $connection->delete($this->getMainTable(), $where);
        return $this;
    }

    /**
     * Delete all guest alerts on website
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param int $customerId
     * @param int $websiteId
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function deleteGuest(\Magento\Framework\Model\AbstractModel $object, $email, $websiteId = null)
    {
        $connection = $this->getConnection();
        $where = [];
        $where[] = $connection->quoteInto('subscriber_email=?', $email);
        if ($websiteId) {
            $where[] = $connection->quoteInto('website_id=?', $websiteId);
        }
        $connection->delete($this->getMainTable(), $where);
        return $this;
    }

    /**
     * Before save action
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {

        if (!$object->getToken()) {
            $token = $this->generateRandomString(30);
            $object->setToken($token);
        }

        return parent::_beforeSave($object);
    }

    public function generateRandomString($length = 10) {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
