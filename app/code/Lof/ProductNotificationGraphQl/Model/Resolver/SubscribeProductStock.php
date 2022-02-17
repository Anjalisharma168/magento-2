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
use Lof\ProductNotification\Api\Data\StockInterface;
use Lof\ProductNotification\Api\Data\StockInterfaceFactory;
use Lof\ProductNotification\Api\SubscribeProductStockManagementInterface;
use Magento\Framework\Api\DataObjectHelper;

class SubscribeProductStock implements ResolverInterface
{
    /**
     * @var SubscribeProductStockManagementInterface
     */
    private $apiRepository;

    /**
     * @var StockInterfaceFactory
     */
    protected $stockFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    public function __construct(
        SubscribeProductStockManagementInterface $apiRepository,
        DataObjectHelper $dataObjectHelper,
        StockInterfaceFactory $stockFactory
    )
    {
        $this->apiRepository = $apiRepository;
        $this->stockFactory = $stockFactory;
        $this->dataObjectHelper  = $dataObjectHelper;
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
        $customer_id = $context->getUserId();
        $store = $context->getExtensionAttributes()->getStore();
        $store_id = $store->getId();
        $website_id = $store->getWebsiteId();
        $args["customer_id"] = (int)$customer_id;
        $args["store_id"] = (int)$store_id;
        $args["website_id"] = (int)$website_id;
        
        $stockDataObject = $this->stockFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $stockDataObject,
            $args,
            StockInterface::class
        );

        $request = $this->apiRepository->postSubscribeProductStock($stockDataObject);  
        if ($request->getAlertStockId() > 0) {
            return __('You subscribed the out of stock for the product. We will notify about product when it has back to stock via your email.');
        } else {
            return __('Error: Please check your information again!');
        }   
    }
}

