<?php

namespace Wyomind\AdvancedInventory\Observer;

class OrderCancelAfter implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->_modelAssignation->cancel($order->getEntityId());
    }
}