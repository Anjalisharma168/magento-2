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

namespace Lof\ProductNotification\Controller\Adminhtml\Price;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context);
        $this->_customerRepository = $customerRepository;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if data sent
        $data = $this->getRequest()->getPostValue();

        if (!$data['product_id']) {
            $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
            $this->messageManager->addSuccess(__('Please select a product.'));
            return $resultRedirect->setPath('*/*/edit', ['alert_price_id' => $this->getRequest()->getParam('alert_price_id')]);
        }
  
        if ($data) {

            $id = $this->getRequest()->getParam('alert_price_id');
            $model = $this->_objectManager->create('Lof\ProductNotification\Model\Price')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This price subscription no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            if (!$data['subscriber_email'] && $data['customer_id']) {
                $customer = $this->_customerRepository->getById($data['customer_id']);
                $data['subscriber_email'] = $customer->getEmail();
            }

            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                $this->messageManager->addSuccess(__('You saved the price subscription.'));
                // clear previously saved data from session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['alert_price_id' => $model->getId()]);
                }
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // save data in session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                // redirect to edit form
                return $resultRedirect->setPath('*/*/edit', ['alert_price_id' => $this->getRequest()->getParam('alert_price_id')]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
