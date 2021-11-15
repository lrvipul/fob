<?php

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Block\Adminhtml;

class Permissions extends \Magento\Backend\Block\Widget\Container
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Backend\Block\Widget\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    public function _construct()
    {
        parent::_construct();
        $this->_controller = 'adminhtml_permissions';
        $this->_blockGroup = 'Wyomind_AdvancedInventory';
        $this->_headerText = __('Manage Permissions');
        $this->addButton('save', ['label' => __('Save all changes'), 'class' => 'save', 'onclick' => "AdvancedInventoryPermissions.save();"]);
        $this->addButton('reset', ['label' => __('Reset'), 'class' => 'delete', 'onclick' => "AdvancedInventoryPermissions.reinit();"]);
        $this->setTemplate('permissions/container.phtml');
        $this->removeButton('add');
    }
    public function getPos()
    {
        return $this->_posModel->getPlaces();
    }
    public function getPermissions()
    {
        return $this->_framework->getDefaultConfig("advancedinventory/system/permissions");
    }
    public function getUsers()
    {
        $usersData = [];
        $users = $this->_userCollection;
        foreach ($users as $user) {
            $user = $this->_userModel->load($user->getUserId());
            $usersData[] = ['id' => $user->getUserId(), 'label' => $user->getUsername() . ' - ' . $user->getFirstname() . " " . $user->getLastname()];
        }
        return $usersData;
    }
}