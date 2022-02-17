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
use Lof\ProductNotification\Api\Data\UnsubscribeRequestInterface;
use Lof\ProductNotification\Api\Data\UnsubscribeRequestInterfaceFactory;
use Lof\ProductNotification\Api\UnsubscribePriceManagementInterface;
use Lof\ProductNotification\Api\UnsubscribePriceAllManagementInterface;
use Magento\Framework\Api\DataObjectHelper;

class UnSubscribeProductSalePrice implements ResolverInterface
{
    /**
     * @var UnsubscribePriceManagementInterface
     */
    private $apiRepository;

    /**
     * @var UnsubscribePriceAllManagementInterface
     */
    private $apiAllRepository;

    /**
     * @var UnsubscribeRequestInterfaceFactory
     */
    protected $requestFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    public function __construct(
        UnsubscribePriceManagementInterface $apiRepository,
        UnsubscribePriceAllManagementInterface $apiAllRepository,
        DataObjectHelper $dataObjectHelper,
        UnsubscribeRequestInterfaceFactory $requestFactory
    )
    {
        $this->apiRepository = $apiRepository;
        $this->apiAllRepository = $apiAllRepository;
        $this->requestFactory = $requestFactory;
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
        $requestDataObject = $this->requestFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $requestDataObject,
            $args,
            UnsubscribeRequestInterface::class
        );
        if(isset($args["website_id"]) && (int)$args["website_id"] > 0){
            $message = $this->apiAllRepository->postUnsubscribePriceAll($requestDataObject);
        }else {
            $message = $this->apiRepository->postUnsubscribePrice($requestDataObject);
        }
        
        return $message;   
    }
}

