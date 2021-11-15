<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Observer;

class SalesOrderCreditmemoRefund implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_modelAssignation->refund($observer);
    }
}