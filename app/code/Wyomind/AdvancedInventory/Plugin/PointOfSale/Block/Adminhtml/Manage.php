<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Plugin\PointOfSale\Block\Adminhtml;

class Manage
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    public function after_construct($subject, $return)
    {
        if (!$this->_helperPermissions->hasAllPermissions()) {
            $subject->removeButton("add");
            $subject->removeButton("import");
            $subject->removeButton("export");
            $this->_messageManager->addError(__("You are not allowed to create a POS/WH, or to import/export a csv file.<br/>Please check the user permissions: ") . "<a href='" . $this->_url->getUrl("advancedinventory/permissions/index") . "'>" . __("Permissions") . "</a>");
        }
        return $return;
    }
}