<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block\Adminhtml\Manage\Edit\Tab;

/**
 * Class Customergroups
 * @package Wyomind\PointOfSale\Block\Adminhtml\Manage\Edit\Tab
 */
class Customergroups extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('pointofsale');
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('customergroup', ['legend' => __('Customer Group Selection')]);
        $_customerGroup = [];
        $allGroups = $this->_groupModel->getCollection()->toOptionHash();
        foreach ($allGroups as $key => $allGroup) {
            $_customerGroup[$key] = ['value' => $key, 'label' => $allGroup];
        }
        array_unshift($_customerGroup, ['value' => "-1", 'label' => __('No Customer Group')]);
        $fieldset->addField('customer_group', 'multiselect', ['name' => 'customer_group[]', 'label' => __('Customer Group'), 'title' => __('Customer Group'), 'class' => 'validate-select', 'required' => true, 'values' => $_customerGroup]);
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Customer Group Selection');
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Customer Group Selection');
    }
    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}