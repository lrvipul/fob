<?php

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Helper;

class Assignation extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_posCollectionFactory = null;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context, \Wyomind\AdvancedInventory\Model\ResourceModel\PointOfSale\CollectionFactory $posCollectionFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_posCollectionFactory = $posCollectionFactory;
        parent::__construct($context);
    }
    public function isUpdatable($status)
    {
        $disallowed = $this->_framework->getStoreConfig("advancedinventory/settings/disallow_assignation_status");
        return !in_array($status, explode(',', $disallowed));
    }
    /* "Creating order in admin" tools */
    public function isAdminQuote()
    {
        return $this->_sessionQuote->getQuote() !== null;
    }
    public function getAdminQuoteStoreId()
    {
        return $this->_sessionQuote->getQuote()->getStoreId();
    }
    public function getAdminQuoteDefaultAssignation()
    {
        $storeId = $this->getAdminQuoteStoreId();
        return $this->_framework->getStoreConfig("advancedinventory/settings/default_assignation_admin_order", $storeId);
    }
    public function isAutoAssignationEnabled()
    {
        return $this->_framework->getDefaultConfig("advancedinventory/settings/autoassign_order") == 1;
    }
    public function isAIEnabled()
    {
        return $this->_framework->getDefaultConfig("advancedinventory/settings/enabled") == 1;
    }
    public function isMultiAssignationEnabled()
    {
        return $this->_framework->getDefaultConfig("advancedinventory/settings/multiple_assignation_enabled") == 1;
    }
    public function getAdminQuotePOSList()
    {
        if ($this->isAdminQuote()) {
            return $this->_posCollectionFactory->create()->getPlacesByStoreId($this->getAdminQuoteStoreId(), null);
        } else {
            return [];
        }
    }
}