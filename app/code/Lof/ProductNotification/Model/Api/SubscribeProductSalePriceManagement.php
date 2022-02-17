<?php
/**
 * Copyright (c) 2019 Landofcoder
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Lof\ProductNotification\Model\Api;

use Lof\ProductNotification\Model\PriceFactory;
use Lof\ProductNotification\Api\Data;
use Lof\ProductNotification\Api\SubscribeProductSalePriceManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Lof\ProductNotification\Model\ResourceModel\Price as ResourcePrice;
use Lof\ProductNotification\Model\ResourceModel\Price\CollectionFactory as PriceCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Authorization\Model\UserContextInterface;


class SubscribeProductSalePriceManagement implements SubscribeProductSalePriceManagementInterface
{

    /**
     * @var ResourcePrice
     */
    protected $resource;

    /**
     * @var PriceFactory
     */
    protected $priceFactory;

    /**
     * @var PriceCollectionFactory
     */
    protected $priceCollectionFactory;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Lof\ProductNotification\Api\Data\PriceInterfaceFactory
     */
    protected $dataPriceFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param ResourcePrice $resource
     * @param PriceFactory $priceFactory
     * @param Data\PriceInterfaceFactory $dataPriceFactory
     * @param PriceCollectionFactory $priceCollectionFactoryy
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Lof\ProductNotification\Helper\Data $helper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourcePrice $resource,
        PriceFactory $priceFactory,
        Data\PriceInterfaceFactory $dataPriceFactory,
        PriceCollectionFactory $priceCollectionFactoryy,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Lof\ProductNotification\Helper\Data $helper
    ) {
        $this->resource = $resource;
        $this->priceFactory = $priceFactory;
        $this->priceCollectionFactoryy = $priceCollectionFactoryy;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPriceFactory = $dataPriceFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->customerSession   = $customerSession;
        $this->helper            = $helper;
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct($productId, $storeId)
    {
        if ($productId) {
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Save price subscrition data
     *
     * @param \Lof\ProductNotification\Api\Data\PriceInterface|Price $price
     * @return \Lof\ProductNotification\Api\Data\PriceInterface
     * @throws CouldNotSaveException
     */
    public function postSubscribeProductSalePrice(\Lof\ProductNotification\Api\Data\PriceInterface $price){
        try {
            if(!$price->getProductId()){
                throw new CouldNotSaveException(
                    __('Could not save the price subscription because product id is not exists')
                );
                return $price;
            }
            if(!$price->getSubscriberEmail()){
                throw new CouldNotSaveException(
                    __('Could not save the price subscription because subscriber email is empty')
                );
                return $price;
            }
            $customer = $this->customerSession->getCustomer();
            if ($this->helper->isLoggedIn()) {
                if(!$price->getCustomerId()){
                    $price->setCustomerId((int)$customer->getId());
                }
                if(!$price->getSubscriberEmail()){
                    $price->setSubscriberEmail($customer->getEmail());
                }
                if(!$price->getSubscriberName()){
                    $price->setSubscriberName($customer->getName());
                }
            }
            
            if(!$this->helper->allowGuestSubscriptionPriceDrop() && !$stock->getCustomerId()){
                throw new CouldNotSaveException(
                    __('The function is not available for guest')
                );
                return $stock;
            }
            $product = $this->_initProduct($price->getProductId(), $price->getStoreId());
            if(!$price->getPrice()){
                $price->setPrice($product->getFinalPrice());
            }
            $price->setStatus(1);
            $this->resource->save($price);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the price subscription: %1', $exception->getMessage()),
                $exception
            );
        }
        return $price;
    }
}
