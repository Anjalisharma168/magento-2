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

namespace Lof\ProductNotification\Controller\Unsubscribe;

use Magento\ProductAlert\Controller\Unsubscribe as UnsubscribeController;
use Magento\Framework\Controller\ResultFactory;

class StockAll extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Lof\ProductNotification\Model\StockFactory $stockFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Lof\ProductNotification\Model\StockFactory $stockFactory
        ) {
        parent::__construct($context);
        $this->stockFactory = $stockFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {

        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!isset($params['token']) || !isset($params['id']) || (isset($params['id']) && !$params['id'])) {
            $this->messageManager->addError(__('Wrong link token and id value.'));
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        $stock = $this->stockFactory->create()->load($params['id']);

        if (!$stock->getId()) {
            $this->messageManager->addError(__('The stock subscriber is not exists.'));
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }
        if ($stock->getToken() != $params['token']) {
            $this->messageManager->addError(__('The link is invalid.'));
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }
        $email_address = isset($params['email'])?$params['email']:'';
        if(isset($params['aid'])){
            $aid = md5($stock->getId() . $stock->getSubscriberEmail());
            if ($aid != $params['aid']) {
                $this->messageManager->addError(__('The aid is invalid.'));
                $resultRedirect->setPath('/');
                return $resultRedirect;
            }
        }else {
            if($email_address != $stock->getSubscriberEmail()){
                $this->messageManager->addError(__('The email address is invalid.'));
                $resultRedirect->setPath('/');
                return $resultRedirect;
            }
        }

        try {
            if ($stock->getCustomerId()) {
                $this->_objectManager->create('Lof\ProductNotification\Model\Stock')
                    ->deleteCustomer(
                        $stock->getCustomerId(),
                        $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                            ->getStore()
                            ->getWebsiteId()
                    );
            } else {
                $this->_objectManager->create('Lof\ProductNotification\Model\Stock')
                    ->deleteGuest(
                        $stock->getSubscriberEmail(),
                        $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                            ->getStore()
                            ->getWebsiteId()
                    );
            }
            $this->messageManager->addSuccess(__('You will no longer receive stock alerts.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to update the alert subscription.'));
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('/');
    }
}
