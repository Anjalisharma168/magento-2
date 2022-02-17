<?php

namespace Restrict\Cart\Block\Index;

class Index extends \Magento\Framework\View\Element\Template {

    /**
     * @param \Magento\Framework\Registry $registry
     */
    protected $_categoryFactory;
    protected $_registry;

    public function __construct(\Magento\Catalog\Block\Product\Context $context,
            \Magento\Framework\Registry $registry, 
            \Magento\Catalog\Model\CategoryFactory $categoryFactory,
            array $data = []) {
        $this->_registry = $registry;
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context, $data);
    }
 
    protected function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function addpopup(){
        $postValues = $this->request->getPostValue();
            $cartItemsCount = $this->cart->getQuote()->getItemsCount();
      
        If($cartItemsCount >= 2)
        {
                $observer->getRequest()->setParam('product', false);
                $this->messageManager->addErrorMessage(__('You can not add product.'));
        }
    }

}