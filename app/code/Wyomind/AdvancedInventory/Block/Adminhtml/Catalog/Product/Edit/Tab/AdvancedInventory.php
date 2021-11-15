<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Block\Adminhtml\Catalog\Product\Edit\Tab;

/**
 * For Magento 2.0.x
 */
class AdvancedInventory extends \Magento\Backend\Block\Template
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_authorization = $context->getAuthorization();
        parent::__construct($context, $data);
        $this->setTemplate('Wyomind_AdvancedInventory::catalog/product/tab/inventory.phtml');
    }
    public function isAuthorized()
    {
        return $this->_authorization->isAllowed('Wyomind_AdvancedInventory::stocks');
    }
    public function getFramework()
    {
        return $this->_framework;
    }
    public function getHelperData()
    {
        return $this->_helperData;
    }
    public function getStockModel()
    {
        return $this->_stockModel;
    }
    public function getPosModel()
    {
        return $this->_posModel;
    }
}