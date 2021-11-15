<?php

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Block\Adminhtml\Stocks;

/**
 *
 * @ignore_var e
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    public $collectionFactory;
    public $posFactory;
    public $posModelFactory;
    public $setsFactory;
    public $productFactory;
    public $websiteFactory;
    protected $_adminStore;
    public $error = "";
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory, \Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\CollectionFactory $posFactory, \Wyomind\PointOfSale\Model\PointOfSaleFactory $posModelFactory, \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Store\Model\WebsiteFactory $websiteFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->collectionFactory = $collectionFactory;
        $this->posFactory = $posFactory;
        $this->posModelFactory = $posModelFactory;
        $this->setsFactory = $setsFactory;
        $this->productFactory = $productFactory;
        $this->websiteFactory = $websiteFactory;
        $this->_adminStore = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        parent::__construct($context, $backendHelper, $data);
    }
    /**
     * @return $this
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId("AdvancedInventoryStocks");
        $this->setDefaultSort("id");
        $this->setDefaultDir("ASC");
        $this->reindex();
    }
    public function reindex()
    {
        $advancedinventoryStock = $this->resource->getTableName("advancedinventory_stock");
        $pointofsale = $this->resource->getTableName("pointofsale");
        $advancedinventoryItem = $this->resource->getTableName("advancedinventory_item");
        $indexView = $this->resource->getTableName("advancedinventory_index");
        $fields = [];
        $sql = "CREATE OR REPLACE VIEW " . $indexView . " AS ( SELECT product_id,";
        $pos = $this->posFactory->create();
        foreach ($pos as $p) {
            $fields[] = "(SELECT quantity_in_stock FROM {$advancedinventoryStock} WHERE place_id=" . $p->getPlaceId() . " AND item_id={$advancedinventoryItem}.id) AS quantity_" . $p->getPlaceId() . "";
            $fields[] = "(SELECT manage_stock FROM {$advancedinventoryStock} WHERE place_id=" . $p->getPlaceId() . " AND item_id={$advancedinventoryItem}.id) AS manage_stock_" . $p->getPlaceId() . "";
            $fields[] = "(SELECT backorder_allowed FROM {$advancedinventoryStock} WHERE place_id=" . $p->getPlaceId() . " AND item_id={$advancedinventoryItem}.id) AS backorder_allowed_" . $p->getPlaceId() . "";
            $fields[] = "(SELECT use_config_setting_for_backorders FROM {$advancedinventoryStock} WHERE place_id=" . $p->getPlaceId() . " AND item_id={$advancedinventoryItem}.id) AS use_config_setting_for_backorders_" . $p->getPlaceId() . "";
            $fields[] = "(SELECT id FROM {$advancedinventoryStock} WHERE place_id=" . $p->getPlaceId() . " AND item_id={$advancedinventoryItem}.id) AS stock_id_" . $p->getPlaceId() . "";
            $fields[] = "(SELECT default_stock_management FROM {$pointofsale} WHERE place_id=" . $p->getPlaceId() . " ) AS default_stock_management_" . $p->getPlaceId() . "";
            $fields[] = "(SELECT default_use_default_setting_for_backorder FROM {$pointofsale} WHERE place_id=" . $p->getPlaceId() . " ) AS default_use_default_setting_for_backorder_" . $p->getPlaceId() . "";
            $fields[] = "(SELECT default_allow_backorder FROM {$pointofsale} WHERE place_id=" . $p->getPlaceId() . " ) AS default_allow_backorder_" . $p->getPlaceId() . "";
        }
        $sql .= implode(",", $fields);
        $sql .= " FROM {$advancedinventoryItem} GROUP BY id )";
        if (count($pos) && $this->resource->getConnection('write')->query($sql)) {
            return true;
        } else {
            return false;
        }
    }
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam("store", 0);
        return $this->_storeManager->getStore($storeId);
    }
    protected function _prepareCollection()
    {
        try {
            $cataloginventoryStockItem = $this->resource->getTableName("cataloginventory_stock_item");
            $advancedinventoryStock = $this->resource->getTableName("advancedinventory_stock");
            $collection = $this->collectionFactory->create();
            $store = $this->_getStore();
            $collection->addAttributeToSelect("sku")->addAttributeToSelect("name")->addAttributeToSelect("attribute_set_id")->addAttributeToSelect("type_id");
            $collection->joinField("qty", $cataloginventoryStockItem, "qty", "product_id=entity_id", "{{table}}.stock_id=1", "left");
            $collection->joinField("stock_status", $cataloginventoryStockItem, "is_in_stock", "product_id=entity_id", "{{table}}.stock_id=1", "left");
            $collection->joinField("use_config_manage_stock", $cataloginventoryStockItem, "use_config_manage_stock", "product_id=entity_id", "{{table}}.stock_id=1", "left");
            $collection->joinField("manage_stock", $cataloginventoryStockItem, "manage_stock", "product_id=entity_id", "{{table}}.stock_id=1", "left");
            $collection->joinField("is_qty_decimal", $cataloginventoryStockItem, "is_qty_decimal", "product_id=entity_id", "{{table}}.stock_id=1", "left");
            $collection->joinField("backorders", $cataloginventoryStockItem, "backorders", "product_id=entity_id", "{{table}}.stock_id=1", "left");
            $collection->joinField("use_config_backorders", $cataloginventoryStockItem, "use_config_backorders", "product_id=entity_id", "{{table}}.stock_id=1", "left");
            $collection->joinField("min_qty", $cataloginventoryStockItem, "min_qty", "product_id=entity_id", "{{table}}.stock_id=1", "left");
            $collection->joinField("use_config_min_qty", $cataloginventoryStockItem, "use_config_min_qty", "product_id=entity_id", "{{table}}.stock_id=1", "left");
            if ($store->getId() != $this->_adminStore) {
                $type = "inner";
                $condition = "multistock_enabled=1";
            } else {
                $type = "left";
                $condition = null;
            }
            $collection->joinField("multistock_enabled", "advancedinventory_item", "multistock_enabled", "product_id=entity_id", $condition, $type);
            if ($store->getId() != $this->_adminStore) {
                $stores = $this->posModelFactory->create()->getPlacesByStoreId($store->getId());
            } else {
                $stores = $this->posModelFactory->create()->getPlaces();
            }
            $fields = [];
            foreach ($stores as $s) {
                if ($this->helperPermissions->isAllowed($s->getPlaceId())) {
                    $fields["quantity_" . $s->getPlaceId()] = "quantity_" . $s->getPlaceId();
                }
            }
            $collection->joinTable("advancedinventory_index", "product_id=entity_id", $fields, null, "left");
            // $fields = [];
            // foreach ($stores as $s) {
            // if ($this->helperPermissions->isAllowed($s->getPlaceId())) {
            // $fields["quantity_" . $s->getPlaceId()] = new \Zend_Db_Expr("(SELECT quantity_in_stock FROM " . $advancedinventoryStock . " WHERE place_id=" . $s->getPlaceId() . " AND item_id=at_multistock_enabled.id)");
            // }
            // }
            // if (count($stores)) {
            // $collection->getSelect()->columns($fields);
            // }
            $collection->addStoreFilter($store);
            $collection->joinAttribute("custom_name", "catalog_product/name", "entity_id", null, "inner", $store->getId());
            $collection->joinAttribute("status", "catalog_product/status", "entity_id", null, "inner", $store->getId());
            $collection->joinAttribute("visibility", "catalog_product/visibility", "entity_id", null, "inner", $store->getId());
            $this->setCollection($collection);
            parent::_prepareCollection();
            $this->getCollection()->addWebsiteNamesToResult();
        } catch (\Exception $e) {
            parent::_prepareCollection();
        }
        return $this;
    }
    /**
     * Prepare columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        try {
            $this->addColumn("item_id", ["header" => __("ID"), "width" => "50px", "type" => "number", "index" => "entity_id"]);
            $this->addColumn("name", ["header" => __("Name"), "index" => "name"]);
            if ($this->_getStore()->getId() != $this->_adminStore) {
                $this->addColumn("custom_name", ["header" => __("Name in %s", $this->_getStore()->getName()), "index" => "custom_name"]);
            }
            $this->addColumn("type", ["header" => __("Type"), "width" => "60px", "index" => "type_id", "type" => "options", "options" => $this->type->getOptionArray()]);
            $sets = $this->setsFactory->create()->setEntityTypeFilter($this->productFactory->create()->getResource()->getTypeId())->load()->toOptionHash();
            $this->addColumn("set_name", ["header" => __("Attrib. Set Name"), "width" => "100px", "index" => "attribute_set_id", "type" => "options", "options" => $sets]);
            $this->addColumn("sku", ["header" => __("SKU"), "width" => "80px", "index" => "sku"]);
            $this->addColumn("visibility", ["header" => __("Visibility"), "width" => "70px", "index" => "visibility", "type" => "options", "options" => $this->visibility->getOptionArray()]);
            $this->addColumn("status", ["header" => __("Status"), "width" => "70px", "index" => "status", "type" => "options", "options" => $this->status->getOptionArray()]);
            if (!$this->_storeManager->isSingleStoreMode() && $this->_getStore()->getId() == $this->_adminStore) {
                $this->addColumn("websites", ["header" => __("Websites"), "width" => "100px", "sortable" => false, "filter" => false, "index" => "websites", "type" => "options", "renderer" => "Wyomind\\AdvancedInventory\\Block\\Adminhtml\\Stocks\\Renderer\\Websites", "options" => $this->websiteFactory->create()->getCollection()->toOptionHash()]);
            }
            if ($this->helperPermissions->hasAllPermissions()) {
                $this->addColumn("stock_status", ["header" => __("Stock Status"), "width" => "70px", "index" => "stock_status", "type" => "options", "renderer" => "Wyomind\\AdvancedInventory\\Block\\Adminhtml\\Stocks\\Renderer\\StockStatus", "options" => [1 => __("In stock"), 0 => __("Out of stock")]]);
            }
            if ($this->_getStore()->getId() != $this->_adminStore || !$this->helperPermissions->hasAllPermissions()) {
                $this->addColumn("qty", ["header" => __("Qty for " . $this->_getStore()->getName()), "type" => "number", "index" => "qty", "align" => "center", "renderer" => "Wyomind\\AdvancedInventory\\Block\\Adminhtml\\Stocks\\Renderer\\StoreViewQty", "width" => "50px", "filter" => false, "sortable" => false, "store_id" => $this->getRequest()->getParam("store", 0)]);
            } else {
                $this->addColumn("qty", ["header" => __("Qty"), "type" => "number", "index" => "qty", "align" => "center", "renderer" => "Wyomind\\AdvancedInventory\\Block\\Adminhtml\\Stocks\\Renderer\\GlobalQty", "width" => "50px"]);
            }
            if ($this->_getStore()->getId() != $this->_adminStore) {
                $stores = $this->posModelFactory->create()->getPlacesByStoreId($this->_getStore()->getStoreId());
            } else {
                $stores = $this->posModelFactory->create()->getPlaces();
            }
            foreach ($stores as $store) {
                if ($this->helperPermissions->isAllowed($store->getPlaceId())) {
                    $marker = null;
                    if ($store->getCustomerGroup() == -1) {
                        $marker .= "<div class='ai-msg alert'>" . __("No customer group") . "</div>";
                    }
                    if (!$store->getStoreId()) {
                        $marker .= "<div class='ai-msg alert'>" . __("No store view") . "</div>";
                    }
                    $this->addColumn("quantity_" . $store->getPlaceId(), ["header" => "<span title='" . $store->getName() . "'>" . $store->getStoreCode() . " | " . $this->helperData->shorten($store->getName()) . $marker . "</span>", "type" => "number", "width" => "50px", "align" => "center", "index" => "quantity_" . $store->getPlaceId(), "place_id" => $store->getPlaceId(), "renderer" => "Wyomind\\AdvancedInventory\\Block\\Adminhtml\\Stocks\\Renderer\\PosQty"]);
                }
            }
            $this->addColumn("action", ["header" => __("Action"), "width" => "100px", "align" => "center", "type" => "action", "filter" => false, "sortable" => false, "renderer" => "Wyomind\\AdvancedInventory\\Block\\Adminhtml\\Stocks\\Renderer\\Actions"]);
        } catch (\Exception $e) {
            $this->addColumn("action", ["header" => __("Action"), "width" => "100px", "align" => "center", "type" => "action", "filter" => false, "sortable" => false]);
        }
        return parent::_prepareColumns();
    }
    /**
     * Row click url
     *
     * @param \Magento\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
    }
    protected function _prepareMassaction()
    {
        if ($this->helperPermissions->hasAllPermissions()) {
            if ($this->_getStore()->getId() == $this->_adminStore) {
                $this->setMassactionIdField("entity_id");
                $this->getMassactionBlock()->setFormFieldName("product_id");
                $this->getMassactionBlock()->addItem("enable_multistock", ["label" => __("Enable multi-stock"), "value" => "enableMultistock", "url" => $this->getUrl("*/*/MassEnable")]);
                $this->getMassactionBlock()->addItem("disable_multistock", ["label" => __("Disable multi-stock"), "value" => "disableMultistock", "url" => $this->getUrl("*/*/MassDisable")]);
            }
        }
        $this->_eventManager->dispatch("adminhtml_catalog_product_grid_prepare_massaction", ["block" => $this]);
        return $this;
    }
}