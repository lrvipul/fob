<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Model\ResourceModel\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Framework\Data\Collection\EntityFactory $entityFactory, \Psr\Log\LoggerInterface $logger, \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy, \Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Eav\Model\Config $eavConfig, \Magento\Framework\App\ResourceConnection $resource, \Magento\Eav\Model\EntityFactory $eavEntityFactory, \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper, \Magento\Framework\Validator\UniversalFactory $universalFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Module\Manager $moduleManager, \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory, \Magento\Catalog\Model\ResourceModel\Url $catalogUrl, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Stdlib\DateTime $dateTime, \Magento\Customer\Api\GroupManagementInterface $groupManagement, \Magento\Framework\DB\Adapter\AdapterInterface $connection = null)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_resource = $resource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $eavConfig, $resource, $eavEntityFactory, $resourceHelper, $universalFactory, $storeManager, $moduleManager, $catalogProductFlatState, $scopeConfig, $productOptionFactory, $catalogUrl, $localeDate, $customerSession, $dateTime, $groupManagement, $connection);
    }
    public function getStockSettings($productId, $placeId, $itemId, $placeIds)
    {
        $connection = $this->_resource;
        $pointofsale = $connection->getTableName("pointofsale");
        $advancedinventoryStock = $connection->getTableName("advancedinventory_stock");
        $advancedinventoryItem = $connection->getTableName("advancedinventory_item");
        $cataloginventoryStockItem = $connection->getTableName("cataloginventory_stock_item");
        $advancedinventoryAssignation = $connection->getTableName("advancedinventory_assignation");
        $manageStock = $this->_framework->getStoreConfig("cataloginventory/item_options/manage_stock");
        $minQty = $this->_framework->getStoreConfig("cataloginventory/item_options/min_qty");
        $backorders = $this->_framework->getStoreConfig("cataloginventory/item_options/backorders");
        $this->addAttributeToSelect('sku')->addAttributeToSelect('name')->addAttributeToSelect('attribute_set_id')->addAttributeToSelect('type_id');
        if ($productId) {
            $this->addFieldToFilter("entity_id", ['eq' => $productId]);
        }
        $this->getSelect()->joinLeft(["cataloginventory_stock_item" => $cataloginventoryStockItem], 'e.entity_id=cataloginventory_stock_item.product_id', ["is_in_stock" => "is_in_stock", "qty" => "qty", "is_qty_decimal" => "is_qty_decimal"], null, 'left')->joinLeft(['advancedinventory_item' => $advancedinventoryItem], 'e.entity_id=advancedinventory_item.product_id', ["item_id" => "id", "multistock_enabled" => "multistock_enabled"], null, 'left');
        $on = null;
        if ($placeId) {
            $this->getSelect()->joinLeft(['pointofsale' => $pointofsale], "pointofsale.place_id ='" . $placeId . "'", ["default_stock_management" => "default_stock_management", "default_allow_backorder" => "default_allow_backorder", "default_use_default_setting_for_backorder" => "default_use_default_setting_for_backorder", "default_backorderable_at_stock_level" => new \Zend_Db_Expr("IF(default_use_default_setting_for_backorder=1," . $backorders . ",IF(default_allow_backorder>0,1,0))"), "default_managed_at_stock_level" => new \Zend_Db_Expr("IF((use_config_manage_stock=1 AND " . $manageStock . "=1) OR (use_config_manage_stock=0 AND cataloginventory_stock_item.manage_stock=1),default_stock_management,0)")], null, 'left');
            $on = " AND advancedinventory_stock.place_id=pointofsale.place_id ";
        }
        if ($itemId && $placeId) {
            $this->getSelect()->joinLeft(['advancedinventory_assignation' => $advancedinventoryAssignation], 'advancedinventory_assignation.item_id="' . $itemId . '" AND advancedinventory_assignation.place_id="' . $placeId . '"', [], null, 'left');
            $this->getSelect()->joinLeft(['advancedinventory_stock' => $advancedinventoryStock], 'e.entity_id=advancedinventory_stock.product_id ' . $on, ["quantity_in_stock" => new \Zend_Db_Expr("SUM(IF(advancedinventory_stock.manage_stock=1,advancedinventory_stock.quantity_in_stock+IFNULL(advancedinventory_assignation.qty_assigned,0),0))")], null, 'left');
        } else {
            $this->getSelect()->joinLeft(['advancedinventory_stock' => $advancedinventoryStock], 'e.entity_id=advancedinventory_stock.product_id ' . $on, ["quantity_in_stock" => new \Zend_Db_Expr("SUM(IF(advancedinventory_stock.manage_stock=1,advancedinventory_stock.quantity_in_stock,0))")], null, 'left');
        }
        $fields = [];
        foreach ($placeIds as $s) {
            if ($s) {
                $fields["use_config_setting_for_backorders_{$s}"] = new \Zend_Db_Expr("(SELECT use_config_setting_for_backorders FROM {$advancedinventoryStock} WHERE place_id=" . $s . " AND item_id=advancedinventory_item.id)");
                $fields["backorder_allowed_{$s}"] = new \Zend_Db_Expr("(SELECT backorder_allowed FROM {$advancedinventoryStock} WHERE place_id=" . $s . " AND item_id=advancedinventory_item.id)");
                $fields["manage_stock_{$s}"] = new \Zend_Db_Expr("(SELECT manage_stock FROM {$advancedinventoryStock} WHERE place_id=" . $s . " AND item_id=advancedinventory_item.id)");
                $fields["quantity_{$s}"] = new \Zend_Db_Expr("(SELECT quantity_in_stock FROM " . $advancedinventoryStock . " WHERE place_id=" . $s . " AND item_id=advancedinventory_item.id)");
                $fields["stock_id_{$s}"] = new \Zend_Db_Expr("(SELECT id FROM {$advancedinventoryStock} WHERE place_id=" . $s . " AND item_id=advancedinventory_item.id)");
                $fields["default_stock_management_{$s}"] = new \Zend_Db_Expr("(SELECT default_stock_management FROM {$pointofsale} WHERE place_id=" . $s . ")");
                $fields["default_allow_backorder_{$s}"] = new \Zend_Db_Expr("(SELECT default_allow_backorder FROM {$pointofsale} WHERE place_id='" . $s . "')");
                $fields["default_use_default_setting_for_backorder_{$s}"] = new \Zend_Db_Expr("(SELECT default_use_default_setting_for_backorder FROM {$pointofsale} WHERE place_id='" . $s . "')");
                $fields["backorders_{$s}"] = new \Zend_Db_Expr("IF((SELECT use_config_setting_for_backorders FROM {$advancedinventoryStock} WHERE place_id='" . $s . "' AND item_id=advancedinventory_item.id)=1," . $backorders . ",IF((SELECT backorder_allowed FROM {$advancedinventoryStock} WHERE place_id='" . $s . "' AND item_id=advancedinventory_item.id)>0,(SELECT backorder_allowed FROM {$advancedinventoryStock} WHERE place_id='" . $s . "' AND item_id=advancedinventory_item.id),0))");
                $fields["is_in_stock_{$s}"] = new \Zend_Db_Expr("IF((SELECT use_config_setting_for_backorders FROM {$advancedinventoryStock} WHERE place_id='" . $s . "' AND item_id=advancedinventory_item.id)=1,IF((SELECT quantity_in_stock FROM " . $advancedinventoryStock . " WHERE place_id='" . $s . "' AND item_id=advancedinventory_item.id)>IF(use_config_min_qty=1," . $minQty . ",cataloginventory_stock_item.min_qty),1,0),1)");
            }
        }
        if (count($placeIds)) {
            $this->getSelect()->columns($fields);
        }
        $this->getSelect()->columns(['backorderable_at_product_level' => new \Zend_Db_Expr("IF(use_config_backorders=1," . $backorders . ",backorders)"), 'backorderable_at_stock_level' => new \Zend_Db_Expr("MAX(IF(advancedinventory_stock.manage_stock=0,1,IF(advancedinventory_stock.use_config_setting_for_backorders=1," . $backorders . ",IF(advancedinventory_stock.backorder_allowed>0,advancedinventory_stock.backorder_allowed,0))))"), 'managed_at_product_level' => new \Zend_Db_Expr("IF(use_config_manage_stock=1,{$manageStock},cataloginventory_stock_item.manage_stock )"), 'managed_at_stock_level' => new \Zend_Db_Expr("IF((use_config_manage_stock=1 AND " . $manageStock . "=1) OR (use_config_manage_stock=0 AND cataloginventory_stock_item.manage_stock=1),MIN(advancedinventory_stock.manage_stock),0)"), 'min_qty' => new \Zend_Db_Expr("IF(use_config_min_qty=1," . $minQty . ",cataloginventory_stock_item.min_qty)")])->group("e.entity_id")->limit(1);
        if (!$this->_framework->isAdmin()) {
            if (count($placeIds)) {
                $this->getSelect()->where("advancedinventory_stock.place_id in (" . implode(',', $placeIds) . ")");
            } else {
                if ($placeId) {
                    $this->getSelect()->where("advancedinventory_stock.place_id='" . $placeId . "'");
                }
            }
        }
        $data = $this->_resource->getConnection()->fetchAll($this->getSelect());
        if (!empty($data[0])) {
            $obj = new \Magento\Framework\DataObject($data[0]);
            return $obj;
        } else {
            return new \Magento\Framework\DataObject();
        }
    }
}