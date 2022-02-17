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

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Stock
 *
 * @package Lof\ProductNotification\Controller\Add
 */
class Stock extends \Magento\Framework\App\Action\Action {
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
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
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Lof\ProductNotification\Helper\Data
     */
    protected $helper;

    /**
     * Stock constructor.
     *
     * @param \Magento\Framework\App\Action\Context                        $context
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator               $formKeyValidator
     * @param \Magento\Catalog\Model\ProductRepository                     $productRepository
     * @param \Magento\Customer\Model\Session                              $customerSession
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Lof\ProductNotification\Helper\Data                         $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Lof\ProductNotification\Helper\Data $helper
    )
    {
        parent::__construct($context);
        $this->_formKeyValidator = $formKeyValidator;
        $this->productRepository = $productRepository;
        $this->customerSession   = $customerSession;
        $this->storeManager      = $storeManager;
        $this->configurable      = $configurable;
        $this->helper            = $helper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ( ! $this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $post           = $this->getRequest()->getParams();
        $product        = $this->_initProduct();

        if ($product) {
            try {
                $super_attribute = [];
                if ($product->getTypeId() == Configurable::TYPE_CODE) {
                    if (isset($post['super_attribute'])) {
                        $super_attribute = $post['super_attribute'];
                        $realProduct = $product->getTypeInstance()->getProductByAttributes($post['super_attribute'], $product);

                        if ($realProduct) {
                            $post['product'] = $realProduct->getId();
                            $params          = [
                                Configurable::TYPE_CODE => $product->getId(),
                            ];

                            $post['params'] = serialize($params);
                        } else {
                            $this->messageManager->addErrorMessage(__('We can\'t update the alert subscription right now. Because the configurable product should choose all required options.'));
                            $resultRedirect->setUrl($this->_redirect->getRedirectUrl());

                            return $resultRedirect;
                        }
                    }
                }


                $model              = $this->_objectManager->get('Lof\ProductNotification\Model\Stock');
                $post['product_id'] = $post['product'];
                $model->setSuperAttribute(json_encode($super_attribute));

                $post = $this->helper->xss_clean_array($post);

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
                      ->setProductId($post['product_id'])
                      ->setMessage($post['message']);

                if (isset($post['params'])) {
                    $model->setParams($post['params']);
                }

                $model->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
                $model->setStatus(1);
                $model->save();

                $this->messageManager->addSuccessMessage(__('Alert subscription has been saved.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());

        return $resultRedirect;
    }

    /**
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            try {
                return $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        return false;
    }
}
