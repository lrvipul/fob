<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Observer;

class SalesOrderPaymentCancel implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getPayment()->getOrder();
        if (!$order) {
            $orderId = $observer->getEvent()->getOrderId();
            $this->_modelAssignation->cancel($orderId);
        } else {
            $this->_modelAssignation->cancel($order->getEntityId());
        }
    }
}