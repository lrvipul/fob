<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Helper;

class Stock extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $stockItemCriteriaFactory = null;
    protected $stockItemFactory = null;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context, \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory, \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockItemFactory = $stockItemFactory;
        parent::__construct($context);
    }
    public function getStockItem($productId)
    {
        $criteria = $this->stockItemCriteriaFactory->create();
        $criteria->setProductsFilter($productId);
        $collection = $this->stockItemRepository->getList($criteria);
        $stockItem = current($collection->getItems());
        return $stockItem;
    }
}