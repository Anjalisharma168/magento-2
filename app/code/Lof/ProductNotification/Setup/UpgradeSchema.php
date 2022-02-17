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

namespace Lof\ProductNotification\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tablePrice = $installer->getTable('lof_product_notification_price');
        $tableStock = $installer->getTable('lof_product_notification_stock');


        //Update for version 1.0.1
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
                /** Add new colums for table price */
                $installer->getConnection()->addColumn(
                    $tablePrice,
                    'params',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => '2M',
                        'nullable' => true,
                        'comment'  => 'Params'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $tablePrice,
                    'super_attribute',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 120,
                        'nullable' => true,
                        'comment'  => 'Product Super Attribute'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $tablePrice,
                    'product_sku',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 100,
                        'nullable' => true,
                        'comment'  => 'Product Sku'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tablePrice,
                    'parent_product_id',
                    [
                        'type'     => Table::TYPE_INTEGER,
                        'length'   => 11,
                        'nullable' => false,
                        'unsigned' => true,
                        'default' => '0',
                        'comment'  => 'Product child id'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tablePrice,
                    'store_id',
                    [
                        'type'     => Table::TYPE_INTEGER,
                        'length'   => 11,
                        'nullable' => false,
                        'unsigned' => true,
                        'default' => '0',
                        'comment'  => 'store id'
                    ]
                );
                
                /** Add new colums for table stock */
                $installer->getConnection()->addColumn(
                    $tableStock,
                    'super_attribute',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 120,
                        'nullable' => true,
                        'comment'  => 'Product Super Attribute'
                    ]
                );
                $installer->getConnection()->addColumn(
                    $tableStock,
                    'product_sku',
                    [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 100,
                        'nullable' => true,
                        'comment'  => 'Product Sku'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $tableStock,
                    'parent_product_id',
                    [
                        'type'     => Table::TYPE_INTEGER,
                        'length'   => 11,
                        'nullable' => false,
                        'unsigned' => true,
                        'default' => '0',
                        'comment'  => 'Product Parent id'
                    ]
                );

                

                $installer->getConnection()->addColumn(
                    $tableStock,
                    'store_id',
                    [
                        'type'     => Table::TYPE_INTEGER,
                        'length'   => 11,
                        'nullable' => false,
                        'unsigned' => true,
                        'default' => '0',
                        'comment'  => 'store id'
                    ]
                );

        }
        //Update for version 1.0.2
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            /**
             * Create table 'lof_product_notification_price_log'
             */
            $setup->getConnection()->dropTable($setup->getTable('lof_product_notification_price_log'));
            $tablePriceLog = $installer->getConnection()->newTable(
                $installer->getTable('lof_product_notification_price_log')
            )->addColumn(
                'alert_price_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product alert price id'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer id'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product id'
            )->addColumn(
                'price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Price amount'
            )->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Website id'
            )->addColumn(
                'add_date',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Product alert add date'
            )->addColumn(
                'last_send_date',
                Table::TYPE_TIMESTAMP,
                null,
                [],
                'Product alert last send date'
            )->addColumn(
                'send_count',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product alert send count'
            )->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product alert status'
            )->addColumn(
                'subscriber_email',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Subscriber Email'
            )->addColumn(
                'subscriber_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Subscriber Name'
            )->addColumn(
                'token',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Token'
            )->addColumn(
                'message',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Message'
            )->addIndex(
                $installer->getIdxName('lof_product_notification_price_log', ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName('lof_product_notification_price_log', ['website_id']),
                ['website_id']
            )->addForeignKey(
                $installer->getFkName('lof_product_notification_price_log', 'product_id', 'catalog_product_entity', 'entity_id'),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment(
                'Product Alert Price'
            );
            $installer->getConnection()->createTable($tablePriceLog);

            /**
             * Create table 'lof_product_notification_stock_log'
             */
            $setup->getConnection()->dropTable($setup->getTable('lof_product_notification_stock_log'));
            $tableStockLog = $installer->getConnection()->newTable(
                $installer->getTable('lof_product_notification_stock_log')
            )->addColumn(
                'alert_stock_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product alert stock id'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer id'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product id'
            )->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Website id'
            )->addColumn(
                'add_date',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Product alert add date'
            )->addColumn(
                'send_date',
                Table::TYPE_TIMESTAMP,
                null,
                [],
                'Product alert send date'
            )->addColumn(
                'send_count',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Send Count'
            )->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product alert status'
            )->addColumn(
                'subscriber_email',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Subscriber Email'
            )->addColumn(
                'subscriber_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Subscriber Name'
            )->addColumn(
                'token',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Token'
            )->addColumn(
                'message',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Message'
            )->addColumn(
                'params',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Params'
            )->addIndex(
                $installer->getIdxName('lof_product_notification_stock_log', ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName('lof_product_notification_stock_log', ['website_id']),
                ['website_id']
            )->addForeignKey(
                $installer->getFkName('lof_product_notification_stock_log', 'product_id', 'catalog_product_entity', 'entity_id'),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment(
                'Product Alert Stock Log'
            );
            $installer->getConnection()->createTable($tableStockLog);

            $tablePriceLog = $setup->getTable('lof_product_notification_price_log');
            $tableStockLog = $setup->getTable('lof_product_notification_stock_log');

            $installer->getConnection()->addColumn(
                $tablePriceLog,
                'params',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'comment'  => 'Params'
                ]
            );
            $installer->getConnection()->addColumn(
                $tablePriceLog,
                'super_attribute',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 120,
                    'nullable' => true,
                    'comment'  => 'Product Super Attribute'
                ]
            );
            $installer->getConnection()->addColumn(
                $tablePriceLog,
                'product_sku',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 100,
                    'nullable' => true,
                    'comment'  => 'Product Sku'
                ]
            );

            $installer->getConnection()->addColumn(
                $tablePriceLog,
                'parent_product_id',
                [
                    'type'     => Table::TYPE_INTEGER,
                    'length'   => 11,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => '0',
                    'comment'  => 'Product child id'
                ]
            );

            $installer->getConnection()->addColumn(
                $tablePriceLog,
                'store_id',
                [
                    'type'     => Table::TYPE_INTEGER,
                    'length'   => 11,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => '0',
                    'comment'  => 'store id'
                ]
            );
            $installer->getConnection()->addColumn(
                $tablePriceLog,
                'log_message',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 120,
                    'nullable' => true,
                    'comment'  => 'Log message'
                ]
            );
            
            /** Add new colums for table stock */
            $installer->getConnection()->addColumn(
                $tableStockLog,
                'super_attribute',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 120,
                    'nullable' => true,
                    'comment'  => 'Product Super Attribute'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableStockLog,
                'log_message',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 120,
                    'nullable' => true,
                    'comment'  => 'Log message'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableStockLog,
                'product_sku',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 100,
                    'nullable' => true,
                    'comment'  => 'Product Sku'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableStockLog,
                'parent_product_id',
                [
                    'type'     => Table::TYPE_INTEGER,
                    'length'   => 11,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => '0',
                    'comment'  => 'Product Parent id'
                ]
            );

            

            $installer->getConnection()->addColumn(
                $tableStockLog,
                'store_id',
                [
                    'type'     => Table::TYPE_INTEGER,
                    'length'   => 11,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => '0',
                    'comment'  => 'store id'
                ]
            );
        }
        
        //Update for version 1.0.3
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            /**
             * Create table 'lof_product_notification_new'
             */
            $setup->getConnection()->dropTable($setup->getTable('lof_product_notification_new'));
            $table = $installer->getConnection()->newTable(
                $installer->getTable('lof_product_notification_new')
            )->addColumn(
                'alert_new_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Product alert new id'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer id'
            )->addColumn(
                'category_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Category id'
            )->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Website id'
            )->addColumn(
                'add_date',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Product alert add date'
            )->addColumn(
                'last_send_date',
                Table::TYPE_TIMESTAMP,
                null,
                [],
                'Product alert last send date'
            )->addColumn(
                'send_count',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product alert send count'
            )->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product alert status'
            )->addColumn(
                'subscriber_email',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Subscriber Email'
            )->addColumn(
                'subscriber_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Subscriber Name'
            )->addColumn(
                'token',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Token'
            )->addColumn(
                'message',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Message'
            )->addIndex(
                $installer->getIdxName('lof_product_notification_new', ['category_id']),
                ['category_id']
            )->addIndex(
                $installer->getIdxName('lof_product_notification_new', ['website_id']),
                ['website_id']
            )->addForeignKey(
                $installer->getFkName('lof_product_notification_new', 'category_id', 'catalog_category_entity', 'entity_id'),
                'category_id',
                $installer->getTable('catalog_category_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment(
                'Product Alert New'
            );
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}