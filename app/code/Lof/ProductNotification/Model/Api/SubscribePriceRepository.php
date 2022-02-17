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

use Lof\ProductNotification\Api\SubscribePriceRepositoryInterface;
use Lof\ProductNotification\Api\Data\PriceSearchResultsInterfaceFactory;
use Lof\ProductNotification\Api\Data\PriceInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Lof\ProductNotification\Model\ResourceModel\Price as ResourcePrice;
use Lof\ProductNotification\Model\ResourceModel\Price\CollectionFactory as PriceCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Lof\ProductNotification\Model\PriceFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\App\ObjectManager;

class SubscribePriceRepository implements SubscribePriceRepositoryInterface
{

    protected $resource;
    protected $priceFactory;
    protected $priceCollectionFactory;
    protected $searchResultsFactory;
    protected $dataObjectHelper;
    protected $dataObjectProcessor;
    protected $dataPriceFactory;
    protected $extensionAttributesJoinProcessor;
    private $storeManager;
    private $collectionProcessor;
    protected $_resource;
    protected $jsonHelper;

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
     * @param PriceInterfaceFactory $dataPriceFactory
     * @param PriceCollectionFactory $priceCollectionFactory
     * @param PriceSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourcePrice $resource,
        PriceFactory $priceFactory,
        PriceInterfaceFactory $dataPriceFactory,
        PriceCollectionFactory $priceCollectionFactory,
        PriceSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        ResourceConnection $Resource,
        Data $jsonHelper
    ) {
        $this->resource = $resource;
        $this->_resource = $Resource;
        $this->priceFactory = $priceFactory;
        $this->priceCollectionFactory = $priceCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPriceFactory = $dataPriceFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->jsonHelper = $jsonHelper;
    }
    /**
     * Get user context.
     *
     * @return UserContextInterface
     */
    private function getUserContext(): UserContextInterface
    {
        if (!$this->userContext) {
            $this->userContext = ObjectManager::getInstance()->get(UserContextInterface::class);
        }

        return $this->userContext;
    }
    /**
     * Get authorization service.
     *
     * @return AuthorizationInterface
     */
    private function getAuthorization(): AuthorizationInterface
    {
        if (!$this->authorization) {
            $this->authorization = ObjectManager::getInstance()->get(AuthorizationInterface::class);
        }

        return $this->authorization;
    }


    /**
     * {@inheritdoc}
     */
    public function getById($subscribePriceId){
        $priceModel = $this->priceFactory->create();
        $priceModel->load($subscribePriceId);
        if (!$priceModel->getId()) {
            throw new NoSuchEntityException(__('Subscribe Price with id "%1" does not exist.', $subscribePriceId));
        }
        return $priceModel;
    }
    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ){
        $collection = $this->priceCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        //$collection->load();
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
    /**
     * {@inheritdoc}
     */
    public function delete($subscribePriceId){
        try {
            $priceModel = $this->priceFactory->create();
            $priceModel->load($subscribePriceId);
            $priceModel->delete();
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Subscribe Price: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function deleteById($subscribePriceId){
        $priceData = $this->getById($subscribePriceId);
        return $this->delete($priceData->getAlertPriceId());
    }
}
