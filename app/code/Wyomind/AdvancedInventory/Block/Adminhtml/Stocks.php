<?php

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Block\Adminhtml;

/**
 *
 * @ignore_var e
 */
class Stocks extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Backend\Block\Widget\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        try {
            $this->_controller = "adminhtml_stocks";
            $this->_blockGroup = "Wyomind_AdvancedInventory";
            $this->_headerText = __("Manage Stocks");
            $this->addButton("save", ["label" => __("Save all changes"), "class" => "save", "onclick" => "InventoryManager.saveStocks('" . $this->getUrl("*/*/save", ["data" => "all", "is_admin" => $this->helperPermissions->hasAllPermissions(), "store_id" => $this->getRequest()->getParam("store", 0)]) . "','all')"]);
            $this->addButton("reset", ["label" => __("Reset"), "class" => "delete", "onclick" => "setLocation('" . $this->getUrl("*/*/index") . "')"]);
        } catch (\Exception $e) {
            $this->_messageManagerClone->addError($this->error);
            $this->removeButton("save");
            $this->removeButton("reset");
        }
        parent::_construct();
        $this->setTemplate("stocks/container.phtml");
        $this->removeButton("add");
    }
    public function isSingleStoreMode()
    {
        if (!$this->_storeManager->isSingleStoreMode()) {
            return false;
        }
        return true;
    }
    public function getFramework()
    {
        return $this->framework;
    }
}