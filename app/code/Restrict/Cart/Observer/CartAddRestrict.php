<?php
namespace Restrict\Cart\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Http\Context as customerSession;

class CartAddRestrict implements ObserverInterface
{
    protected $cart;
    protected $messageManager;
    protected $redirect;
    protected $request;
    protected $product;
    protected $customerSession;
    public function __construct(
        RedirectInterface $redirect,
        Cart $cart,
        ManagerInterface $messageManager,
        RequestInterface $request,
        Product $product,
       customerSession $session
	)
    {
        $this->redirect = $redirect;
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->product = $product;
        $this->customerSession = $session;
    }
  
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    
$postValues = $this->request->getPostValue();
        	$cartItemsCount = $this->cart->getQuote()->getItemsCount();
      
     	If($cartItemsCount >= 2)
		{
                $observer->getRequest()->setParam('product', false);
                $this->messageManager->addErrorMessage(__('You can not add product.'));
		}
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