<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductNotificationGraphQl\Model\Resolver\MySubscribeProductStock;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Retrieves the sort fields data
 */
class SortFields implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $sortFieldsOptions = [
            ['label' => "alert_stock_id", 'value' => "alert_stock_id"],
            ['label' => "product_id", 'value' => "product_id"],
            ['label' => "price", 'value' => "price"],
            ['label' => "add_date", 'value' => "add_date"],
            ['label' => "send_count", 'value' => "send_count"],
            ['label' => "status", 'value' => "status"],
            ['label' => "product_sku", 'value' => "product_sku"],
            ['label' => "last_send_date", 'value' => "last_send_date"],
            ['label' => "store_id", 'value' => "store_id"],
            ['label' => "parent_product_id", 'value' => "parent_product_id"]
        ];
        
        $data = [
            'default' => "alert_stock_id",
            'options' => $sortFieldsOptions,
        ];

        return $data;
    }
}
