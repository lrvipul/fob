<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Plugin\Stockqty;

/**
 * Product stock qty abstract block
 */
class DefaultStockqty
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    /**
     * Retrieve product stock qty
     * The $subject param is an abstract class, a child is expected
     * 
     * @param Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject
     * @param Product $product
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundGetProductStockQty($subject, \Closure $proceed, $product)
    {
        $inventory = $this->_stockModel->getStockByProductIdAndStoreIds($product->getId(), array($product->getStore()->getStoreId()));
        if ($inventory->getMultistockEnabled() == 1) {
            return $inventory->getQty();
        } else {
            return $proceed($product);
        }
    }
}