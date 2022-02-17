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

namespace Lof\ProductNotification\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;

class Plugin
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirector;

    /**
     * @param CustomerSession      $customerSession 
     * @param ScopeConfigInterface $config          
     * @param RedirectInterface    $redirector      
     */
    public function __construct(
        CustomerSession $customerSession,
        ScopeConfigInterface $config,
        RedirectInterface $redirector
    ) {
        $this->customerSession = $customerSession;
        $this->config = $config;
        $this->redirector = $redirector;
    }

    /**
     * Perform customer authentication and lofformbuilder feature state checks
     *
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param RequestInterface $request
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function beforeDispatch(\Magento\Framework\App\ActionInterface $subject, RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $subject->getActionFlag()->set('', 'no-dispatch', true);
            $this->customerSession->setBeforeModuleName('productNotification');
            $this->customerSession->setBeforeControllerName('index');
            $this->customerSession->setBeforeAction('index');
        }
        // if (!$this->config->isSetFlag('requestforquote/general/enable')) {
        //     throw new NotFoundException(__('Page not found.'));
        // }
    }
}
