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

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;
use Lof\ProductNotification\Api\Data\PriceSearchResultsInterfaceFactory;
use Lof\ProductNotification\Api\Data\PriceInterfaceFactory;
use Lof\ProductNotification\Model\ResourceModel\Price as ResourcePrice;
use Lof\ProductNotification\Model\ResourceModel\Price\CollectionFactory as PriceCollectionFactory;
use Lof\ProductNotification\Model\PriceFactory;
use Lof\ProductNotification\Api\Data\StockSearchResultsInterfaceFactory;
use Lof\ProductNotification\Api\Data\StockInterfaceFactory;
use Lof\ProductNotification\Model\ResourceModel\Stock as ResourceStock;
use Lof\ProductNotification\Model\ResourceModel\Stock\CollectionFactory as StockCollectionFactory;
use Lof\ProductNotification\Model\StockFactory;

class CustomerManagement implements \Lof\ProductNotification\Api\CustomerManagementInterface
{
    protected $priceResource;
    protected $priceFactory;
    protected $priceCollectionFactory;
    protected $priceSearchResultsFactory;
    protected $dataPriceFactory;

    protected $stockResource;
    protected $stockFactory;
    protected $stockCollectionFactory;
    protected $stockSearchResultsFactory;
    protected $dataStockFactory;

    protected $dataObjectHelper;
    protected $dataObjectProcessor;
    private $storeManager;
    private $collectionProcessor;
    protected $_resource;
    protected $jsonHelper;

    /**
     * @param ResourcePrice $priceResource
     * @param PriceFactory $priceFactory
     * @param PriceInterfaceFactory $dataPriceFactory
     * @param PriceCollectionFactory $priceCollectionFactory
     * @param PriceSearchResultsInterfaceFactory $priceSearchResultsFactory
     * @param ResourceStock $resource
     * @param StockFactory $stockFactory
     * @param StockInterfaceFactory $dataStockFactory
     * @param StockCollectionFactory $stockCollectionFactory
     * @param StockSearchResultsInterfaceFactory $stockSearchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ResourceConnection $Resource
     * @param Data $jsonHelper
     */
    public function __construct(
        ResourcePrice $priceResource,
        PriceFactory $priceFactory,
        PriceInterfaceFactory $dataPriceFactory,
        PriceCollectionFactory $priceCollectionFactory,
        PriceSearchResultsInterfaceFactory $priceSearchResultsFactory,
        ResourceStock $stockResource,
        StockFactory $stockFactory,
        StockInterfaceFactory $dataStockFactory,
        StockCollectionFactory $stockCollectionFactory,
        StockSearchResultsInterfaceFactory $stockSearchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        ResourceConnection $Resource,
        Data $jsonHelper
    ) {
        $this->priceResource = $priceResource;
        $this->priceFactory = $priceFactory;
        $this->priceCollectionFactory = $priceCollectionFactory;
        $this->priceSearchResultsFactory = $priceSearchResultsFactory;
        $this->dataPriceFactory = $dataPriceFactory;

        $this->stockResource = $stockResource;
        $this->stockFactory = $stockFactory;
        $this->stockCollectionFactory = $stockCollectionFactory;
        $this->stockSearchResultsFactory = $stockSearchResultsFactory;
        $this->dataStockFactory = $dataStockFactory;

        $this->dataObjectHelper = $dataObjectHelper;
        $this->_resource = $Resource;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @var UserContextInterface
     */
    private $userContext;
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * {@inheritdoc}
     */
    public function getListPrice($customerId, \Magento\Framework\Api\SearchCriteriaInterface $criteria){
        $collection = $this->priceCollectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $collection->addFieldtoFilter("customer_id", $customerId);

        $searchResults = $this->priceSearchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getListStock($customerId, \Magento\Framework\Api\SearchCriteriaInterface $criteria){
        $collection = $this->stockCollectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $collection->addFieldtoFilter("customer_id", $customerId);

        $searchResults = $this->priceSearchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getStockById($customerId, $subscribeStockId){
        if(!$customerId)
            throw new NoSuchEntityException(__('Please logged in your account.'));
        $stockModel = $this->stockFactory->create();
        $stockModel->load($subscribeStockId);
        if (!$stockModel->getId()  || ((int)$customerId != $stockModel->getCustomerId()) ) {
            throw new NoSuchEntityException(__('Subscribe Stock with id "%1" does not exist.', $subscribeStockId));
        }
        return $stockModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceById($customerId, $subscribePriceId){
        if(!$customerId)
            throw new NoSuchEntityException(__('Please logged in your account.'));
        $priceModel = $this->priceFactory->create();
        $priceModel->load($subscribePriceId);
        if (!$priceModel->getId() || ((int)$customerId != $priceModel->getCustomerId())) {
            throw new NoSuchEntityException(__('Subscribe Price with id "%1" does not exist.', $subscribePriceId));
        }

        return $priceModel;
    }

    /**
     * {@inheritdoc}
     */
    public function deletePrice($customerId, $subscribePriceId){
        try {
            $priceModel = $this->priceFactory->create();
            $priceModel->load($subscribePriceId);
            if (!$priceModel->getId() || ((int)$customerId != $priceModel->getCustomerId())) {
                throw new NoSuchEntityException(__('Subscribe Price with id "%1" does not exist.', $subscribePriceId));
            }else{
                $priceModel->delete();
            }
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Subscribe Price: %1',
                $exception->getMessage()
            ));
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteStock($customerId, $subscribeStockId){
        try {
            $stockModel = $this->stockFactory->create();
            $stockModel->load($subscribeStockId);
            if (!$stockModel->getId() || ((int)$customerId != $stockModel->getCustomerId())) {
                throw new NoSuchEntityException(__('Subscribe Stock with id "%1" does not exist.', $subscribeStockId));
            }else{
                $stockModel->delete();
            }
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Subscribe Stock: %1',
                $exception->getMessage()
            ));
            return false;
        }
        return true;
    }
}
