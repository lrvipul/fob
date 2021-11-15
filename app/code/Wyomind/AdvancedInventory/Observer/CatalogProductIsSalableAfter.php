<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Observer;

class CatalogProductIsSalableAfter implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->_storeManager->getStore()->getStoreId();
        $placeIds = $this->_modelPos->getPlacesByStoreId($storeId);
        if ($this->_frameworkData->getStoreConfig('advancedinventory/settings/enabled')) {
            $rtn = false;
            $product = $observer->getProduct();
            if (in_array($product->getTypeId(), ['downloadable', 'virtual'])) {
                return;
            }
            if ($this->_stockHelper->getStockItem($product->getId())) {
                $stockStatus = $this->_stockHelper->getStockItem($product->getId())->getIsInStock();
                if ($product->getStatus() == 2 || !$stockStatus || $product->getDisableAddToCart()) {
                    $observer->getSalable()->setIsSalable(false);
                    return;
                }
            }
            if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                return;
            } else {
                if ($this->_modelStock->isMultiStockEnabledByProductId($product->getId())) {
                    $rtn = $this->isAvailable($product, $placeIds);
                } else {
                    $rtn = null;
                }
            }
            if ($rtn !== null) {
                $observer->getSalable()->setIsSalable($rtn);
            }
        }
    }
    public function isAvailable($product, $placeIds)
    {
        foreach ($placeIds->getData() as $pos) {
            $productId = $product->getId();
            $stock = $this->_modelStock->getStockSettings($productId, $pos['place_id']);
            if ($stock->getStockStatus() && $stock->getData('manage_stock_' . $pos['place_id']) < 2) {
                return true;
            }
        }
        return false;
    }
}