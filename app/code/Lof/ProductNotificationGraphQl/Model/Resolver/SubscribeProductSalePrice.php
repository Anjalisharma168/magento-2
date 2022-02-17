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
use Lof\ProductNotification\Api\Data\PriceInterface;
use Lof\ProductNotification\Api\Data\PriceInterfaceFactory;
use Lof\ProductNotification\Api\SubscribeProductSalePriceManagementInterface;
use Magento\Framework\Api\DataObjectHelper;

class SubscribeProductSalePrice implements ResolverInterface
{
    /**
     * @var SubscribeProductSalePriceManagementInterface
     */
    private $apiRepository;

    /**
     * @var PriceInterfaceFactory
     */
    protected $priceFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    public function __construct(
        SubscribeProductSalePriceManagementInterface $apiRepository,
        DataObjectHelper $dataObjectHelper,
        PriceInterfaceFactory $priceFactory
    )
    {
        $this->apiRepository = $apiRepository;
        $this->priceFactory = $priceFactory;
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
        
        $priceDataObject = $this->priceFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $priceDataObject,
            $args,
            PriceInterface::class
        );

        $request = $this->apiRepository->postSubscribeProductSalePrice($priceDataObject);  
        if ($request->getAlertPriceId() > 0) {
            return __('You subscribed the price drop for the product. We will notify about product when it has sales price via your email.');
        } else {
            return __('Error: Please check your information again!');
        }   
    }
}

