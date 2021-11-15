<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block\Adminhtml\Manage;

/**
 * Class Edit
 * @package Wyomind\PointOfSale\Block\Adminhtml\Manage
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Backend\Block\Widget\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    /**
     *
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Wyomind_PointOfSale';
        $this->_controller = 'adminhtml_manage';
        parent::_construct();
        $this->removeButton('save');
        $this->removeButton('reset');
        $this->updateButton('delete', 'label', __('Delete'));
        $this->addButton('save', ['label' => __('Save'), 'class' => 'save', 'onclick' => 'require(["jquery"], function($) {$("#back_i").val("1"); $("#edit_form").submit();});']);
    }
}