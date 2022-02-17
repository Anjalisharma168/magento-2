<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductNotificationGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

class MySubscribeProductStock implements ResolverInterface
{

    private $mySubscribeProductStockDataProvider;

    /**
     * @param DataProvider\MySubscribeProductStock $mySubscribeProductStockRepository
     */
    public function __construct(
        DataProvider\MySubscribeProductStock $mySubscribeProductStockDataProvider
    ) {
        $this->mySubscribeProductStockDataProvider = $mySubscribeProductStockDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $mySubscribeProductStockData = $this->mySubscribeProductStockDataProvider->getMySubscribeProductStock(
            $args, 
            $context
        );
        return $mySubscribeProductStockData;
    }
}

