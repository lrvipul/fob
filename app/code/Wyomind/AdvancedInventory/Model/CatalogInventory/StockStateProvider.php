<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Model\CatalogInventory;

class StockStateProvider
{
    protected $_posFactory = null;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Wyomind\PointOfSale\Model\PointOfSaleFactory $posFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_posFactory = $posFactory;
    }
    public function beforeCheckQuoteItemQty($subsject, $item)
    {
        if (!$this->_modelStock->isMultiStockEnabledByProductId($item->getProductId())) {
            return;
        }
        $storeId = $this->_storeManager->getStore()->getStoreId();
        if ($this->_assignationHelper->isAdminQuote()) {
            $storeId = $this->_assignationHelper->getAdminQuoteStoreId();
        }
        $places = $this->_posFactory->create()->getPlacesByStoreId($storeId);
        $placeIds = [];
        foreach ($places as $place) {
            $placeIds[] = $place->getPlaceId();
        }
        $inventory = $this->_modelStock->getStockSettings($item->getProductId(), false, $placeIds);
        $qty = 0;
        $backOrderableAtStockLevel = 0;
        foreach ($places as $place) {
            if ($place->getManageInventory() == 2) {
                $qtyP = "quantity_" . $place->getId() . "";
                $manageStock = "manage_stock_" . $place->getId() . "";
                $backorders = "backorders_" . $place->getId() . "";
                $backorderAllowed = "backorder_allowed_" . $place->getId() . "";
                $isInStock = "is_in_stock_" . $place->getId() . "";
                $warehouses = explode(',', $place->getWarehouses());
                $stocksWarehouses = $this->_modelStock->getStockSettings($item->getProductId(), false, $warehouses);
                $inventory[$isInStock] = 0;
                $inventory[$qtyP] = 0;
                $inventory[$backorderAllowed] = 0;
                $inventory[$backorders] = false;
                foreach ($warehouses as $warehouse) {
                    $inventory[$qtyP] += $stocksWarehouses['quantity_' . $warehouse];
                    $inventory[$isInStock] |= $stocksWarehouses['is_in_stock_' . $warehouse];
                    $inventory[$manageStock] |= $stocksWarehouses['manage_stock_' . $warehouse];
                    $inventory[$backorderAllowed] = max($inventory[$backorders], $stocksWarehouses['backorder_allowed_' . $warehouse]);
                    $inventory[$backorders] |= $stocksWarehouses['backorders_' . $warehouse];
                }
            }
            $qtyInStock = 'quantity_' . $place->getPlaceId() . "";
            $manageStock = 'manage_stock_' . $place->getPlaceId() . "";
            if ($inventory[$manageStock] != 2) {
                $qty += (int) $inventory[$qtyInStock] - (int) $inventory['min_qty'];
                if (!$inventory[$manageStock]) {
                    $qty += INF;
                }
                if (isset($backorderAllowed)) {
                    $backOrderableAtStockLevel = max($inventory[$backorderAllowed], $backOrderableAtStockLevel);
                }
            }
        }
        $item->setBackorders($backOrderableAtStockLevel);
        if ($backOrderableAtStockLevel) {
            $item->setUseConfigBackorders(0);
        }
        $qty += $item->getMinQty();
        $item->setQty($qty);
    }
}