<?php
namespace Cut\Paste\Block\Adminhtml\Order\View;

use Magento\Sales\Api\Data\OrderInterface;
class DisplayCustomValue extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        OrderInterface $orderInterface,
        array $data = []
    ) {
        $this->orderInterface = $orderInterface;
        parent::__construct($context, $data);
    }
    public function getOrdertime(){
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderInterface->load($orderId);

        return $order->getOrdertime();
    }
     public function getOrdercomment(){
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderInterface->load($orderId);

        return $order->getOrdercomment();
    }
     public function getOrderdate(){
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderInterface->load($orderId);

        return $order->getOrderdate();
    }

}