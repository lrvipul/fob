<?php

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Helper;

class Permissions extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_permissions = [];
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context);
    }
    public function getUserPermissions()
    {
        if ($this->_permissions == null) {
            $permissions = $this->_objectManager->create('Magento\\Framework\\Json\\Helper\\Data')->jsonDecode($this->_framework->getDefaultConfig("advancedinventory/system/permissions"));
            if ($this->_auth->isLoggedIn()) {
                $userId = $this->_auth->getUser()->getUserId();
                if (isset($permissions[$userId])) {
                    $this->_permissions = $permissions[$userId];
                }
            }
        }
        return $this->_permissions;
    }
    public function getUserPermissionsByUserName($userId)
    {
        $permissions = $this->_objectManager->create('Magento\\Framework\\Json\\Helper\\Data')->jsonDecode($this->_framework->getDefaultConfig("advancedinventory/system/permissions"));
        if (isset($permissions[$userId])) {
            return $permissions[$userId];
        }
        return [];
    }
    public function hasAllPermissions()
    {
        if (in_array("all", $this->getUserPermissions())) {
            return true;
        }
        return false;
    }
    public function canSeeUnassignedOrders()
    {
        if (in_array("0", $this->_permissions[1])) {
            return true;
        }
        return false;
    }
    public function isAllowed($pos)
    {
        if ($this->hasAllPermissions() || in_array($pos, $this->getUserPermissions())) {
            return true;
        }
        return false;
    }
}