<?php
 
namespace Dotsquares\Newsletter\Setup;
 
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
 
class InstallSchema implements InstallSchemaInterface {
	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
		$setup->startSetup();
 
		$table = $setup->getTable('newsletter_subscriber');
 
		$setup->getConnection()->addColumn(
			$table,
			'full_name',
			[
				'type' => Table::TYPE_TEXT,
				'nullable' => true,
				'comment' => 'Name',
			]
		);
 
		$setup->endSetup();
	}
}