<?php

namespace Wyomind\AdvancedInventory\Block\Adminhtml\PointOfSale\Manage\Edit\Tab;

class Inventory extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry, $formFactory, $data);
    }
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('pointofsale');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('');
        $fieldset = $form->addFieldset('ai_settings_1', ['legend' => __('Inventory Settings')]);
        $model->load($this->getRequest()->getParam('place_id'));
        $fieldset->addField('manage_inventory', 'select', ['label' => __('Stock Management'), 'name' => 'manage_inventory', 'class' => 'manage_inventory', "options" => [1 => __("Enabled"), 0 => __("Disabled"), 2 => __("Use warehouses stocks")]]);
        $warehouses = [];
        foreach ($this->posCollection as $pos) {
            if ($pos->getStatus() == 0) {
                $warehouses[] = ['value' => $pos->getId(), 'label' => $pos->getName()];
            }
        }
        $fieldset->addField('warehouses', 'multiselect', ['name' => 'warehouses[]', 'label' => __('Warehouses'), 'title' => __('Warehouses'), 'class' => 'validate-select', 'required' => true, 'values' => $warehouses]);
        $fieldset->addField('manage_inventory_backup', 'hidden', ['name' => 'manage_inventory_backup', 'class' => 'manage_inventory']);
        $model->setData('manage_inventory_backup', $model->getManageInventory());
        $fieldset->addField('use_assignation_rules', 'select', ['label' => __('Assignation method'), 'name' => 'use_assignation_rules', 'class' => 'use_assignation_rules', "options" => [0 => "Do not assign any order", 1 => "Assign orders when product is available", 2 => "Assign orders depending on specific rules"], 'note' => 'Assign order to one/several warehouses']);
        $fieldset->addField('inventory_assignation_rules', 'textarea', ['label' => __('Assignation rules'), 'name' => 'inventory_assignation_rules', 'class' => 'inventory_assignation_rules', 'note' => 'Assign to this inventory all orders which shipping address matches with this rule']);
        $fieldset->addField('inventory_notification', 'text', ['label' => __('Order notifications'), 'name' => 'inventory_notification', 'class' => 'inventory_notification', 'note' => 'Email recipients separated with a comma (,)']);
        $link = $this->_urlBuilder->getDirectUrl('advancedinventory/rss/feed') . '/wh/' . $this->getRequest()->getParam('id');
        $fieldset->addField('rss_feed', 'link', ['label' => __('Low stock notification feed'), 'name' => 'rss_feed', 'note' => '<a target="_blank" href="' . $link . '">' . $link . '</a>']);
        $fieldset->addField('stock_status_message', 'text', ['label' => "", 'name' => 'stock_status_message', 'note' => "<span class='pos_label' style='display:none'>" . __('In stock status message') . "</span><span class='wh_label' style='display:none'>" . __('Stock status message') . "</span>" . __("Message that displays on the product page when the product is <b>available</b> in this stock.<br/>Default value: <i>In stock</i>")]);
        $fieldset->addField('stock_status_message_backorder', 'text', ['label' => __('Backorder status message '), 'name' => 'stock_status_message_backorder', 'note' => 'Message that displays on the product page when the product is <b>backorderable</b> in this stock.<br/>Default value: <i>Backorder</i>']);
        $fieldset->addField('stock_status_message_out_of_stock', 'text', ['label' => __('Out of stock status message '), 'name' => 'stock_status_message_out_of_stock', 'note' => 'Message that displays on the product page when the product is <b>not available</b> in this stock.<br/>Default value: <i>Out of stock</i>']);
        $fieldset = $form->addFieldset('ai_settings_2', ['legend' => __('Default settings for products')]);
        $fieldset->addField('default_stock_management', 'select', ['label' => __('Quantity management'), 'name' => 'default_stock_management', "options" => [1 => __("Enabled"), 0 => __("Disabled")]]);
        if (!$this->getRequest()->getParam("id")) {
            $model->setData('default_stock_management', 1);
        }
        $fieldset->addField('default_use_default_setting_for_backorder', 'select', ['label' => __('Use config setting for backorders'), 'name' => 'default_use_default_setting_for_backorder', "options" => [1 => __('yes'), 0 => __('no')], "selected" => 1]);
        $fieldset->addField('default_allow_backorder', 'select', ['label' => __('Backorders status'), 'name' => 'default_allow_backorder', "options" => [0 => __('No backorders'), 1 => __('Allow Qty below 0'), 2 => __('Allow Qty below 0 and Notify Customer')]]);
        if ($this->getRequest()->getParam('id')) {
            $fieldset->addField('posupdate', 'checkbox', ['name' => 'posupdate', "class" => "action-default scalable", 'label' => __('Save and apply'), 'note' => __("Save and apply stock setting to all multi-stock products for this point of sale/warehouse")]);
            $model->setData('posupdate', "on");
        }
        $this->setChild('form_after', $this->getLayout()->createBlock('Magento\\Backend\\Block\\Widget\\Form\\Element\\Dependence')->addFieldMap('manage_inventory', 'manage_inventory')->addFieldMap('use_assignation_rules', 'use_assignation_rules')->addFieldMap('inventory_assignation_rules', 'inventory_assignation_rules')->addFieldMap('default_stock_management', 'default_stock_management')->addFieldMap('default_use_default_setting_for_backorder', 'default_use_default_setting_for_backorder')->addFieldMap('default_allow_backorder', 'default_allow_backorder')->addFieldMap('posupdate', 'posupdate')->addFieldMap('rss_feed', 'rss_feed')->addFieldMap('warehouses', 'warehouses')->addFieldMap('inventory_notification', 'inventory_notification')->addFieldMap('stock_status_message_out_of_stock', 'stock_status_message_out_of_stock')->addFieldMap('stock_status_message_backorder', 'stock_status_message_backorder')->addFieldMap('status', 'status')->addFieldDependence('stock_status_message_out_of_stock', 'status', 1)->addFieldDependence('stock_status_message_backorder', 'status', 1)->addFieldDependence('use_assignation_rules', 'manage_inventory', 1)->addFieldDependence('inventory_assignation_rules', 'manage_inventory', 1)->addFieldDependence('default_stock_management', 'manage_inventory', 1)->addFieldDependence('default_use_default_setting_for_backorder', 'manage_inventory', 1)->addFieldDependence('default_allow_backorder', 'manage_inventory', 1)->addFieldDependence('posupdate', 'manage_inventory', 1)->addFieldDependence('rss_feed', 'manage_inventory', 1)->addFieldDependence('inventory_notification', 'manage_inventory', 1)->addFieldDependence('warehouses', 'manage_inventory', 2)->addFieldDependence('inventory_assignation_rules', 'use_assignation_rules', 2)->addFieldDependence('default_use_default_setting_for_backorder', 'default_stock_management', 1)->addFieldDependence('default_allow_backorder', 'default_stock_management', 1)->addFieldDependence('default_allow_backorder', 'default_use_default_setting_for_backorder', 0));
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
    public function getTabLabel()
    {
        return __('Inventory Settings');
    }
    public function getTabTitle()
    {
        return __('Inventory Settings');
    }
    public function canShowTab()
    {
        return true;
    }
    public function isHidden()
    {
        return false;
    }
}