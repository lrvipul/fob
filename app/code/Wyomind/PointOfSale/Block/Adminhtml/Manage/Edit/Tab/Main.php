<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block\Adminhtml\Manage\Edit\Tab;

/**
 * Class Main
 * @package Wyomind\PointOfSale\Block\Adminhtml\Manage\Edit\Tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);
        if ($model->getPlaceId()) {
            $fieldset->addField('place_id', 'hidden', ['name' => 'place_id']);
        }
        // ===================== action flags ==================================
        // save and continue flag
        $fieldset->addField('back_i', 'hidden', ['name' => 'back_i', 'value' => '']);
        $fieldset->addField('store_code', 'text', ['label' => __('Code (internal use)'), 'name' => 'store_code', 'class' => 'required-entry', 'required' => true]);
        $fieldset->addField('name', 'text', ['label' => __('Name'), 'name' => 'name', 'class' => 'required-entry', 'required' => true]);
        $fieldset->addField('status', 'select', ['label' => __('Type of display'), 'name' => 'status', 'values' => [['value' => 0, 'label' => __('Warehouse (not visible on the frontend)')], ['value' => 1, 'label' => __('Point of Sales')]]]);
        $fieldset->addField('visible_store_locator', 'select', ['name' => 'visible_store_locator', 'label' => __('Visible on store locator'), 'values' => [['value' => 1, 'label' => __('Yes')], ['value' => 0, 'label' => __('No')]]]);
        if ($this->framework->moduleIsEnabled("Wyomind_AdvancedInventory")) {
            $fieldset->addField('visible_product_page', 'select', ['name' => 'visible_product_page', 'label' => __('Visible on product page'), 'values' => [['value' => 1, 'label' => __('Yes')], ['value' => 0, 'label' => __('No')]]]);
        }
        if ($this->framework->moduleIsEnabled("Wyomind_PickupAtStore")) {
            $fieldset->addField('visible_checkout', 'select', ['name' => 'visible_checkout', 'label' => __('Visible on checkout page (Collect in store option)'), 'values' => [['value' => 1, 'label' => __('Yes')], ['value' => 0, 'label' => __('No')]]]);
        }
        $fieldset->addField('position', 'text', ['label' => __('Order of display'), 'name' => 'position', 'class' => 'required-entry validate-number', 'required' => true]);
        $fieldset->addField('latitude', 'text', ['label' => __('Latitude'), 'class' => 'required-entry validate-number', 'name' => 'latitude', 'required' => true]);
        $fieldset->addField('longitude', 'text', ['label' => __('Longitude'), 'class' => 'required-entry validate-number', 'name' => 'longitude', 'required' => true, 'after_element_html' => '
                    <div style="margin:10px 0;"><b>' . __("Find the coordinates with Google Map:") . '</b> </div>
                    <div id="map" ></div>']);
        $this->setChild('form_after', $this->getLayout()->createBlock('Magento\\Backend\\Block\\Widget\\Form\\Element\\Dependence')->addFieldMap('status', 'status')->addFieldMap('visible_store_locator', 'visible_store_locator')->addFieldMap('visible_product_page', 'visible_product_page')->addFieldMap('visible_checkout', 'visible_checkout')->addFieldDependence('visible_store_locator', 'status', 1)->addFieldDependence('visible_product_page', 'status', 1)->addFieldDependence('visible_checkout', 'status', 1));
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('General Information');
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('General Information');
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