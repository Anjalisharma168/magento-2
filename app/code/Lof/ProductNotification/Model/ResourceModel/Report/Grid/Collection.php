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

namespace Lof\ProductNotification\Model\ResourceModel\Report\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Customer\Model\Visitor;
use Magento\Framework\Api;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    /**
     * Value of seconds in one minute
     */
    const SECONDS_IN_MINUTE = 60;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var Visitor
     */
    protected $visitorModel;

    /**
     * Collection constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param null|string $resourceModel
     * @param Visitor $visitorModel
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable,
        $resourceModel,
        Visitor $visitorModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->date = $date;
        $this->visitorModel = $visitorModel;
        $this->_objectManager = $objectManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $productNameAttributeId = $this->_objectManager->create('Magento\Eav\Model\Config')->getAttribute(\Magento\Catalog\Model\Product::ENTITY, \Magento\Catalog\Api\Data\ProductInterface::NAME)->getAttributeId();
        $connection = $this->getConnection();
        $select = $connection->select()->from(['stock' => $this->getTable('lof_product_notification_stock')], 'product_id');
        $stockProductIds = $connection->fetchCol($select);
        $connection = $this->getConnection();
        $select = $connection->select()->from(['price' => $this->getTable('lof_product_notification_price')], 'product_id');
        $priceProductIds = $connection->fetchCol($select);
        $productIds = array_unique(array_merge($stockProductIds, $priceProductIds));

        $this->getSelect()
        ->joinLeft(
            array(
                'price' => $this->getTable('lof_product_notification_price')),
                'main_table.entity_id = price.product_id ',
                array(
                    'number_price' => 'COUNT(price.product_id)'
                )
            )
        ->joinLeft(
            array(
                'stock' => $this->getTable('lof_product_notification_stock')),
                'main_table.entity_id = stock.product_id ',
                array(
                    'number_stock' => 'COUNT(stock.product_id)'
                )
            )
        ->joinLeft(
            ['product_varchar' => $this->getTable('catalog_product_entity_varchar')],
            "main_table.entity_id = product_varchar.entity_id AND product_varchar.attribute_id = $productNameAttributeId",
            ['product_name' => 'product_varchar.value']
        )
        ->where('main_table.entity_id in (?)', $productIds);
        $this->getSelect()->where('product_varchar.store_id = ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $this->getSelect()->group('main_table.entity_id');
        return $this;
    }

    /**
     * Add field filter to collection
     *
     * @param string|array $field
     * @param string|int|array|null $condition
     * @return \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'visitor_type') {
            $field = 'customer_id';
            if (is_array($condition) && isset($condition['eq'])) {
                $condition = $condition['eq'] == Visitor::VISITOR_TYPE_CUSTOMER ? ['gt' => 0] : ['null' => true];
            }
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
