<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_ProductNotification
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\ProductNotification\Controller\Stock;

use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends \Lof\ProductNotification\Controller\AbstractIndex
{

    /**
     * Customer visitor
     *
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\ProductNotification\Model\ResourceModel\Stock\CollectionFactory $stockCollectionFactory
        ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry     = $registry;
        parent::__construct($context);
        $this->_formKeyValidator = $formKeyValidator;
        $this->_customerVisitor  = $customerVisitor;
        $this->_storeManager     = $storeManager;
        $this->productRepository = $productRepository;
        $this->_customerSession  = $customerSession;
        $this->_stockCollectionFactory  = $stockCollectionFactory;
    }

    /**
     * Add item to compare list
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setRefererUrl();
        }

        $productId = (int)$this->getRequest()->getParam('product');


        if ($productId && $this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getCustomer()->getId();
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            try {
                $product = $this->productRepository->getById($productId, false, $websiteId);
                if ($product) {
                    $stockNotification = $this->_stockCollectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('product_id', $productId)
                    ->getFirstItem();
                    if ($stockNotification->getProductId()==$productId) {
                        $stockNotification->delete();
                        $this->messageManager->addSuccess(__('The subscription has been deleted.'));
                    }
                }
            } catch (NoSuchEntityException $e) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            }
        }
        return $resultRedirect->setRefererOrBaseUrl();
    }
}
