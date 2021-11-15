<?php

/* *
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Block\Adminhtml\Catalog\Product\Edit;

/**
 * For Magento >= 2.1
 */
class AdvancedInventory extends \Magento\Ui\Component\Form\Fieldset
{
    protected $_fieldFactory = null;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Framework\View\Element\UiComponent\ContextInterface $context, \Magento\Ui\Component\Form\FieldFactory $fieldFactory, array $components = [], array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $components, $data);
        $this->_fieldFactory = $fieldFactory;
    }
    public function getChildComponents()
    {
        $fieldName = "inventory]";
        // parameters
        $productId = $this->_requestInterface->getParam('id');
        $storeId = $this->_requestInterface->getParam('store');
        // POS ids
        //if (!$storeId) {
        $pointOfSales = $this->_posModel->getCollection();
        //} else {
        //    $pointOfSales = $this->_posModel->getPlacesByStoreId($storeId);
        //}
        $ids = [];
        foreach ($pointOfSales as $pointOfSale) {
            if ($pointOfSale->getManageInventory() != 2) {
                $ids[] = $pointOfSale->getPlaceId();
            }
        }
        $firstPointOfSales = reset($ids);
        $stocks = $this->_stockModel->getStockSettings($productId, null, [$firstPointOfSales]);
        //$stocks = $this->_stockModel->getStockSettings($productId, null, $ids);
        // Mage version ?
        $fieldInstance = $this->_fieldFactory->create();
        $fieldInstance->setData(['config' => ['label' => "", 'value' => $this->_framework->getMagentoVersion(), 'formElement' => 'hidden'], 'name' => $fieldName . "[mage"]);
        $fieldInstance->prepare();
        $this->addComponent($fieldName . "[mage", $fieldInstance);
        // auto update stock status ?
        $fieldInstance = $this->_fieldFactory->create();
        $fieldInstance->setData(['config' => ['label' => "", 'value' => $this->_framework->getStoreConfig("advancedinventory/settings/auto_update_stock_status"), 'formElement' => 'hidden'], 'name' => $fieldName . "[auto_update_stock_status"]);
        $fieldInstance->prepare();
        $this->addComponent($fieldName . "[auto_update_stock_status", $fieldInstance);
        // product id
        $fieldInstance = $this->_fieldFactory->create();
        $fieldInstance->setData(['config' => ['label' => "", 'value' => $productId, 'formElement' => "hidden"], 'name' => $fieldName . "[product_id"]);
        $fieldInstance->prepare();
        $this->addComponent($fieldName . "[product_id", $fieldInstance);
        if ($productId == null || !$this->_dataHelper->isProductAllowed($productId)) {
            return parent::getChildComponents();
        }
        // manage local stocks
        $fieldInstance = $this->_fieldFactory->create();
        if (!$storeId) {
            $fieldInstance->setData(['config' => ['label' => __('Manage Local Stocks'), 'value' => $stocks->getMultistockEnabled(), 'formElement' => 'select', 'options' => [["value" => 0, "label" => __('No')], ["value" => 1, "label" => __('Yes')]]], 'name' => $fieldName . "[multistock"]);
        } else {
            $fieldInstance->setData(['config' => ['label' => __('Manage Local Stocks'), 'value' => $stocks->getMultistockEnabled(), 'formElement' => 'hidden'], 'name' => $fieldName . "[multistock"]);
        }
        $fieldInstance->prepare();
        $this->addComponent($fieldName . "[multistock", $fieldInstance);
        // POS/Warehouses
        $counter = 0;
        foreach ($pointOfSales as $pointOfSale) {
            if ($pointOfSale->getManageInventory() == 2) {
                continue;
            }
            // **************** START HOT FIX ****************
            $stocks = $this->_stockModel->getStockSettings($productId, null, [$pointOfSale->getPlaceId()]);
            // **************** END HOT FIX ****************
            $storeIds = explode(',', $pointOfSale->getStoreId());
            $posWh = $fieldName . "[pos_wh][" . $pointOfSale->getPlaceId() . "]";
            if ($pointOfSale->getStatus() == 0) {
                $visibility = __('Warehouse (hidden)');
            } else {
                $visibility = __('Point of Sales (visible)');
            }
            $getQuantity = "getQuantity" . $pointOfSale->getPlaceId();
            $getStockId = "getStockId" . $pointOfSale->getPlaceId();
            $getManageStock = "getManageStock" . $pointOfSale->getPlaceId();
            $getDefaultStockManagement = "getDefaultStockManagement" . $pointOfSale->getPlaceId();
            $getUseConfigSettingForBackorders = "getUseConfigSettingForBackorders" . $pointOfSale->getPlaceId();
            $getBackorderAllowed = "getBackorderAllowed" . $pointOfSale->getPlaceId();
            $getDefaultUseDefaultSettingForBackorder = "getDefaultUseDefaultSettingForBackorder" . $pointOfSale->getPlaceId();
            $getDefaultAllowBackorder = "getDefaultAllowBackorder" . $pointOfSale->getPlaceId();
            // manage stock ?
            if ($stocks->{$getManageStock}() == 1 && $stocks->{$getStockId}() || !$stocks->{$getStockId}() && $stocks->{$getDefaultStockManagement}()) {
                $value = 1;
            } elseif (!$stocks->{$getManageStock}() && $stocks->{$getStockId}() || !$stocks->{$getStockId}() && !$stocks->{$getDefaultStockManagement}()) {
                $value = 0;
            } elseif ($stocks->{$getManageStock}() == 2 && $stocks->{$getStockId}() || !$stocks->{$getStockId}() && !$stocks->{$getDefaultStockManagement}()) {
                $value = 2;
            }
            $fieldInstance = $this->_fieldFactory->create();
            $fieldInstance->setData(['config' => ['label' => $pointOfSale->getName() . " - [" . $visibility . ", code : " . $pointOfSale->getStoreCode() . "]", 'value' => $value, 'formElement' => $storeId && !in_array($storeId, $storeIds) ? 'hidden' : 'select', 'options' => [["value" => 2, "label" => __("Stock disabled")], ["value" => 0, "label" => __("No stock management")], ["value" => 1, "label" => __("Stock enabled")]]], 'name' => $posWh . "[manage_stock"]);
            $fieldInstance->prepare();
            $this->addComponent($posWh . "[manage_stock", $fieldInstance);
            // qty
            $fieldInstance = $this->_fieldFactory->create();
            $fieldInstance->setData(['config' => ['label' => " ", 'value' => $this->_dataHelper->qtyFormat($stocks->{$getQuantity}(), $stocks->getIsQtyDecimal()), 'formElement' => $storeId && !in_array($storeId, $storeIds) ? 'hidden' : 'input'], 'name' => $posWh . "[qty"]);
            $fieldInstance->prepare();
            $this->addComponent($posWh . "[qty", $fieldInstance);
            // backorders
            $backorders_checked = null;
            $backorders_value = null;
            $backorders_disabled = null;
            if (!$stocks->{$getStockId}()) {
                $backorders_checked = $stocks->{$getDefaultUseDefaultSettingForBackorder}();
                $backorders_value = $stocks->{$getDefaultAllowBackorder}();
                $backorders_disabled = $stocks->{$getDefaultUseDefaultSettingForBackorder}() ? true : false;
            } else {
                $backorders_checked = $stocks->{$getUseConfigSettingForBackorders}();
                $backorders_value = $stocks->{$getBackorderAllowed}();
                $backorders_disabled = $stocks->{$getUseConfigSettingForBackorders}() ? true : false;
            }
            $fieldInstance = $this->_fieldFactory->create();
            $fieldInstance->setData(['config' => ['label' => " ", 'value' => $backorders_value, 'formElement' => $storeId && !in_array($storeId, $storeIds) ? 'hidden' : 'select', 'options' => [["value" => 0, "label" => __('No backorders')], ["value" => 1, "label" => __('Allow Qty below 0')], ["value" => 2, "label" => __('Allow Qty below 0 and Notify Customer')]]], 'name' => $posWh . "[backorder_allowed"]);
            $fieldInstance->prepare();
            $this->addComponent($posWh . "[backorder_allowed", $fieldInstance);
            // use config backorders setting
            $fieldInstance = $this->_fieldFactory->create();
            $fieldInstance->setData(['config' => ['label' => " ", 'description' => __('Use Config Settings'), 'checked' => $backorders_checked, 'value' => $backorders_checked ? "1" : "0", 'valueMap' => ["true" => "1", "false" => 0], 'formElement' => $storeId && !in_array($storeId, $storeIds) ? 'hidden' : 'checkbox'], 'name' => $posWh . "[use_config_setting_for_backorders"]);
            $fieldInstance->prepare();
            $this->addComponent($posWh . "[use_config_setting_for_backorders", $fieldInstance);
        }
        return parent::getChildComponents();
    }
}