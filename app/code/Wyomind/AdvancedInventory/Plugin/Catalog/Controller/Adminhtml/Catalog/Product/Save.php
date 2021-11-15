<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\AdvancedInventory\Plugin\Catalog\Controller\Adminhtml\Catalog\Product;

/**
 * 
 * @ignore_var e
 */
class Save {

    public $framework = null;
    public $helperData = null;
    public $stockModel = null;
    public $stockHelper = null;
    public $journalHelper = null;
    public $permissionsHelper = null;
    public $posModel = null;
    
    protected $_cacheHelper;

    public function __construct(
    \Wyomind\Framework\Helper\Module $framework,
            \Wyomind\AdvancedInventory\Helper\Data $helperData,
            \Wyomind\AdvancedInventory\Model\Stock $stockModel,
            \Wyomind\AdvancedInventory\Model\Item $itemModel,
            \Wyomind\PointOfSale\Model\PointOfSale $posModel,
            \Wyomind\AdvancedInventory\Helper\Stock $stockHelper,
            \Wyomind\AdvancedInventory\Helper\Journal $journalHelper,
            \Wyomind\AdvancedInventory\Helper\Permissions $permissionsHelper,
            \Wyomind\AdvancedInventory\Helper\Cache $cacheHelper
        ) {

        $this->framework = $framework;
        $this->helperData = $helperData;
        $this->stockModel = $stockModel;
        $this->itemModel = $itemModel;
        $this->stockHelper = $stockHelper;
        $this->journalHelper = $journalHelper;
        $this->permissionsHelper = $permissionsHelper;
        $this->posModel = $posModel;
        $this->_cacheHelper = $cacheHelper;
    }

    public function beforeExecute($subject) {
        if ($subject->getRequest()->getParam("id") == null) {
            return;
        }
        try {


            $journal = $this->journalHelper;
            $post = $subject->getRequest()->getPostValue();
            if (version_compare($this->framework->getMagentoVersion(), "2.1", ">=") && isset($post['product'])) {
                $post = $post['product'];
            }
            $productId = $subject->getRequest()->getParam("id");

            if (isset($post["inventory"])) {
                $productData = (object) $post["inventory"];
                if (isset($productData->pos_wh)) {
                    $posWh = (array) $productData->pos_wh;
                } else {
                    $posWh = [];
                }
                $stock = $this->stockModel->getStockSettings($productId, false, array_keys($posWh));
            }
            $storeId = $subject->getRequest()->getParam("store_id");
            $isAdmin = $this->permissionsHelper->hasAllPermissions();

            if (isset($post["inventory"]) && isset($productData->multistock)) {
                if ($productData->multistock === "1") {
                    $qty = 0;
                    $substract = 0;

                    foreach ($posWh as $posId => $pos) {
                        $pos = (object) $pos;
                        if ($storeId || !$isAdmin) {
                            $posQty = "getQuantity" . $posId;
                            $substract+=$stock->$posQty();
                        }
                        if ($pos->manage_stock == 1) {
                            $qty += $pos->qty;
                        }
                    }
                    if ($storeId || !$isAdmin) {
                        $qty = $stock->getQty() - $substract + $qty;
                    }
                } else {
                    /*$post = $subject->getRequest()->getPostValue();*/
                    if (isset($post/*["product"]*/["quantity_and_stock_status"]["qty"])) {
                        $qty = $subject->getRequest()->getPostValue()["product"]["quantity_and_stock_status"]["qty"];
                    } else {
                        $qty = 0;
                    }
                }

                if ($productData->multistock === "1") {
                    $data = [
                        "id" => $stock->getItemId(),
                        "product_id" => $productId,
                        "multistock_enabled" => true,
                    ];
                    // Insert / update advancedinventory_item
                    $itemId = $stock->getItemId();
                    if ($stock->getMultistockEnabled() != $productData->multistock) {
                        $this->journalHelper->insertRow($journal::SOURCE_PRODUCT, $journal::ACTION_MULTISTOCK, "P#" . $productId, ["from" => "off", "to" => "on"]);

                        $this->itemModel->setData($data)->save();
                        $itemId = $this->itemModel->getId();
                    }




                    foreach ($posWh as $posId => $pos) {
                        $pos = (object) $pos;

                        $stockId = "getStockId" . $posId;
                        $posQty = "getQuantity" . $posId;
                        $posManageStock = "getManageStock" . $posId;
                        $posBackorderAllowed = "getBackorderAllowed" . $posId;
                        $posUseConfigSettingForBackorders = "getUseConfigSettingForBackorders" . $posId;

                        $data = [
                            "id" => $stock->$stockId(),
                            "item_id" => $itemId,
                            "place_id" => $posId,
                            "product_id" => $productId,
                            "quantity_in_stock" => $pos->qty,
                            "manage_stock" => $pos->manage_stock,
                            "backorder_allowed" => (isset($pos->backorder_allowed)) ? $pos->backorder_allowed : 0,
                            "use_config_setting_for_backorders" => (isset($pos->use_config_setting_for_backorders)) ? ($pos->use_config_setting_for_backorders == "on" ||$pos->use_config_setting_for_backorders == "1") ? 1 : 0 : 0
                        ];


                        $updated = false;

                        if ($stock->$posQty() != $pos->qty) {
                            $updated = true;
                            $this->journalHelper->insertRow($journal::SOURCE_PRODUCT, $journal::ACTION_STOCK_QTY, "P#" . $productId . ",W#" . $posId, ["from" => $stock->$posQty(), "to" => $pos->qty]);
                        }
                        if ($stock->$posManageStock() != $pos->manage_stock) {
                            $updated = true;
                            $this->journalHelper->insertRow($journal::SOURCE_PRODUCT, $journal::ACTION_STOCK_MANAGE_QTY, "P#" . $productId . ",W#" . $posId, ["from" => $stock->$posManageStock(), "to" => $pos->manage_stock]);
                        }
                        if ($stock->$posBackorderAllowed() != $data["backorder_allowed"]) {
                            $updated = true;
                            $this->journalHelper->insertRow($journal::SOURCE_PRODUCT, $journal::ACTION_STOCK_BACKORDERS, "P#" . $productId . ",W#" . $posId, ["from" => $stock->$posBackorderAllowed(), "to" => $data["backorder_allowed"]]);
                        }
                        if ($stock->$posUseConfigSettingForBackorders() != $data["use_config_setting_for_backorders"]) {
                            $updated = true;
                            $this->journalHelper->insertRow($journal::SOURCE_PRODUCT, $journal::ACTION_STOCK_USE_CONFIG_BACKORDERS, "P#" . $productId . ",W#" . $posId, ["from" => $stock->$posUseConfigSettingForBackorders(), "to" => $data["use_config_setting_for_backorders"]]);
                        }
                        if ($updated) {
                            $this->stockModel->load($data["id"])->setData($data)->save();
                        }
                    }
                } elseif ($stock->getMultistockEnabled() > $productData->multistock) { // Delete advancedinventory_item entry
                    $this->journalHelper->insertRow($journal::SOURCE_PRODUCT, $journal::ACTION_MULTISTOCK, "P#" . $productId, ["from" => "on", "to" => "off"]);
                    $this->itemModel->setId($stock->getItemId())->delete();
                }


                // Update backorders status
                $product = $subject->getRequest()->getPostValue("product");
                $inventory = $this->stockHelper->getStockItem($productId);
                $stock = $this->stockModel->getStockSettings($productId);
                if ($productData->multistock) {
                    $product["stock_data"]["use_config_backorders"] = false;
                    $product["stock_data"]["backorders"] = $stock->getBackorderableAtStockLevel();
                }
                // Update is in stock status
                if ($this->framework->getStoreConfig("advancedinventory/settings/auto_update_stock_status") || !$isAdmin) {
                    $stockStatus = $stock->getStockStatus();
                } else {
                    $tmpIsInStock = $subject->getRequest()->getPostValue();
                    if (isset($tmpIsInStock["product"]["quantity_and_stock_status"]["is_in_stock"])) {
                        $stockStatus = $tmpIsInStock["product"]["quantity_and_stock_status"]["is_in_stock"];
                    } else {
                        $stockStatus = $stock->getStockStatus();
                    }
                }

                if ($inventory != null) {
                    if ($stockStatus != $inventory->getIsInStock()) {
                        $product["quantity_and_stock_status"]["is_in_stock"] = $stockStatus;
                        $status = ($stockStatus) ? "In stock" : "Out of stock";
                        $previousStatus = ($inventory->getIsInStock()) ? "In stock" : "Out of stock";
                        $this->journalHelper->insertRow($journal::SOURCE_PRODUCT, $journal::ACTION_IS_IN_STOCK, "P#" . $productId, ["from" => $previousStatus, "to" => $status]);
                    }

                    // Update qty
                    if (isset($post["inventory"])) {
                        if ($inventory->getQty() != $qty) {
                            $this->journalHelper->insertRow($journal::SOURCE_PRODUCT, $journal::ACTION_QTY, "P#" . $productId, ["from" => $inventory->getQty(), "to" => $qty]);
                            $product["quantity_and_stock_status"]["qty"] = $qty;
                            $product["stock_data"]["qty"] = $qty;
                        }
                    }
                }
                $this->_cacheHelper->purgeCache($productId);
                $subject->getRequest()->setPostValue("product", $product);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
