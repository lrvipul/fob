<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Block\Adminhtml\Sales\Order\Create;

class Items extends \Magento\Framework\View\Element\Template
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Framework\View\Element\Template\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    public function isMultiAssignationEnabled()
    {
        return $this->_framework->getDefaultConfig("advancedinventory/settings/multiple_assignation_enabled") == 1;
    }
}