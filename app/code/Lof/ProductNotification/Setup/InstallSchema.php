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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $quoteTable = $installer->getTable('quote');
        $installer->getConnection()->addColumn(
            $quoteTable,
            'pnf_subscriber_email',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'comment'  => 'ProductNotification Subscriber Email'
            ]
        );

        $installer->getConnection()->addColumn(
            $quoteTable,
            'pnf_subscriber_name',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'comment'  => 'ProductNotification Subscriber Name'
            ]
        );

        /**
         * Create table 'lof_product_notification_price'
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_product_notification_price'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_product_notification_price')
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
            $installer->getIdxName('lof_product_notification_price', ['product_id']),
            ['product_id']
        )->addIndex(
            $installer->getIdxName('lof_product_notification_price', ['website_id']),
            ['website_id']
        )->addForeignKey(
            $installer->getFkName('lof_product_notification_price', 'product_id', 'catalog_product_entity', 'entity_id'),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Product Alert Price'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'lof_product_notification_stock'
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_product_notification_stock'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_product_notification_stock')
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
            $installer->getIdxName('lof_product_notification_stock', ['product_id']),
            ['product_id']
        )->addIndex(
            $installer->getIdxName('lof_product_notification_stock', ['website_id']),
            ['website_id']
        )->addForeignKey(
            $installer->getFkName('lof_product_notification_stock', 'product_id', 'catalog_product_entity', 'entity_id'),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Product Alert Stock'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
