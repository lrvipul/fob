<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Observer;

class SaveInventory implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_request->getParam("isAjax")) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $framework = $om->get("\\Wyomind\\Framework\\Helper\\Module");
            $framework->setDefaultConfig("cataloginventory/options/can_subtract", 0);
            $this->_managerInterface->addWarning(__('Advanced Inventory notice : `Decrease Stock When Order is Placed` must be disabled.'));
        }
    }
}