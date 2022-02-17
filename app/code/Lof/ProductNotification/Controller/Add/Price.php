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

namespace Lof\ProductNotification\Controller\Add;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Price extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Lof\ProductNotification\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session                    $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator     $formKeyValidator
     * @param \Magento\Checkout\Model\Cart                       $cart
     * @param \Magento\Catalog\Api\ProductRepositoryInterface    $productRepository
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param \Lof\ProductNotification\Helper\Data               $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\ProductNotification\Helper\Data $helper
        ) {
        parent::__construct($context);
        $this->_formKeyValidator = $formKeyValidator;
        $this->productRepository = $productRepository;
        $this->customerSession   = $customerSession;
        $this->storeManager      = $storeManager;
        $this->helper            = $helper;
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $backUrl = $this->getRequest()->getParam(Action::PARAM_NAME_URL_ENCODED);
        $post    = $this->getRequest()->getParams();
        $product = $this->_initProduct();

        if (!$backUrl || !$product) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        if ($product) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            try {
                $model = $this->_objectManager->get('Lof\ProductNotification\Model\Price');
                //$model->setData($post);

                $post = $this->helper->xss_clean_array($post);

                $super_attribute = isset($post['super_attribute']) ? $post['super_attribute'] : [];
                $model->setSuperAttribute(json_encode($super_attribute));

                $customer = $this->customerSession->getCustomer();
                if ($this->helper->isLoggedIn()) {
                    $email = $customer->getEmail();
                    $name  = $customer->getName();
                } else {
                    $email = isset($post['subscriber_email']) && $post['subscriber_email'] ? $post['subscriber_email'] : '';
                    $name  = isset($post['subscriber_name']) && trim($post['subscriber_name']) ? $post['subscriber_name'] : __('Anonymous');
                }

                $model->setSubscriberEmail($email);
                $model->setSubscriberName($name);

                $model->setCustomerId($customer->getId())
                        ->setProductId($post['product'])
                        ->setPrice($product->getFinalPrice())
                        ->setMessage($post['message'])
                        ->setWebsiteId($this->storeManager->getStore()->getWebsiteId());

                if (isset($post['params'])) {
                    $model->setParams($post['params']);
                }
                $model->setStatus(1);
                $model->save();

                $this->messageManager->addSuccess(__('You saved the alert subscription.'));
            } catch (NoSuchEntityException $noEntityException) {
                $this->messageManager->addError(__('There are not enough parameters.'));
                if ($this->isInternal($backUrl)) {
                    $resultRedirect->setUrl($backUrl);
                } else {
                    $resultRedirect->setPath('/');
                }
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t update the alert subscription right now.'));
            }
        }
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }
}
