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

use Lof\ProductNotification\Model\StockFactory;
use Lof\ProductNotification\Api\Data;
use Lof\ProductNotification\Api\SubscribeProductStockManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Lof\ProductNotification\Model\ResourceModel\Stock as ResourceStock;
use Lof\ProductNotification\Model\ResourceModel\Stock\CollectionFactory as StockCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class SubscribeProductStockManagement implements SubscribeProductStockManagementInterface
{

     /**
     * @var ResourcePrice
     */
    protected $resource;

    /**
     * @var StockFactory
     */
    protected $stockFactory;

    /**
     * @var StockCollectionFactory
     */
    protected $stockCollectionFactory;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Lof\ProductNotification\Api\Data\StockInterfaceFactory
     */
    protected $dataStockFactory;

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
     * @param ResourceStock $resource
     * @param StockFactory $stockFactory
     * @param Data\StockInterfaceFactory $dataStockFactory
     * @param StockCollectionFactory $stockCollectionFactoryy
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
        ResourceStock $resource,
        StockFactory $stockFactory,
        Data\StockInterfaceFactory $dataStockFactory,
        StockCollectionFactory $stockCollectionFactoryy,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Lof\ProductNotification\Helper\Data $helper
    ) {
        $this->resource = $resource;
        $this->stockFactory = $stockFactory;
        $this->stockCollectionFactoryy = $stockCollectionFactoryy;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataStockFactory = $dataStockFactory;
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
     * Save stock subscrition data
     *
     * @param \Lof\ProductNotification\Api\Data\StockInterface|Stock $stock
     * @return \Lof\ProductNotification\Api\Data\StockInterface
     * @throws CouldNotSaveException
     */
    public function postSubscribeProductStock($stock)
    {
        try {
            if(!$stock->getProductId()){
                throw new CouldNotSaveException(
                    __('Could not save the stock subscription because product id is not exists')
                );
                return $stock;
            }
            if(!$stock->getSubscriberEmail()){
                throw new CouldNotSaveException(
                    __('Could not save the stock subscription because subscriber email is empty')
                );
                return $stock;
            }
            $customer = $this->customerSession->getCustomer();
            if ($this->helper->isLoggedIn()) {
                if(!$stock->getCustomerId()){
                    $stock->setCustomerId((int)$customer->getId());
                }
                if(!$stock->getSubscriberEmail()){
                    $stock->setSubscriberEmail($customer->getEmail());
                }
                if(!$stock->getSubscriberName()){
                    $stock->setSubscriberName($customer->getName());
                }
            }
            if(!$this->helper->allowGuestSubscriptionStock() && !$stock->getCustomerId()){
                throw new CouldNotSaveException(
                    __('The function is not available for guest')
                );
                return $stock;
            }
            $product = $this->_initProduct($stock->getProductId(), $stock->getStoreId());
            if ($product->getTypeId()==Configurable::TYPE_CODE) {
                if ($super_attribute = $stock->getSuperAttribute()) {
                    $realdProduct    = $product->getTypeInstance()->getProductByAttributes($super_attribute, $product);
                    $stock_status = $realdProduct->getQuantityAndStockStatus();
                    $is_in_stock = isset($stock_status['is_in_stock'])?(int)$stock_status['is_in_stock']:1;
                    $qty = isset($stock_status['qty'])?(int)$stock_status['qty']:0;
                    if($is_in_stock || (0 < $qty)){
                        throw new CouldNotSaveException(
                            __('We can\'t add this stock subscription, because the product is not out of stock.')
                        );
                        return $stock;
                    }
                    if($realdProduct) {
                        $stock->setProductId($realdProduct->getId());
                        $stock->setParentProductId($product->getId());
                        $params = [
                            Configurable::TYPE_CODE => $product->getId()
                        ];
                        $stock->setParams(serialize($params));
                    } else {
                        throw new CouldNotSaveException(
                            __('We can\'t update the alert subscription right now. Because the configurable product should choose all required options.')
                        );
                        return $stock;
                    }
                }
            }else{
                $stock_status = $product->getQuantityAndStockStatus();
                $is_in_stock = isset($stock_status['is_in_stock'])?(int)$stock_status['is_in_stock']:1;
                $qty = isset($stock_status['qty'])?(int)$stock_status['qty']:0;
                if($is_in_stock || (0 < $qty)){
                    throw new CouldNotSaveException(
                        __('We can\'t add this stock subscription, because the product is not out of stock.')
                    );
                    return $stock;
                }
            }
            $stock->setStatus(1);
            $this->resource->save($stock);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the stock subscription: %1', $exception->getMessage()),
                $exception
            );
        }
        return $stock;
    }
}
