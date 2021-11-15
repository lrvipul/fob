<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Block;

/**
 * Class Store
 * @package Wyomind\PointOfSale\Block
 */
class StoreStock extends \Magento\Framework\View\Element\Template
{

    protected $_aiStockModel;
    protected $_registry;
    protected $_aiAssigntnHlpr;
    protected $_posModel;
    protected $_storeId = null;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Wyomind\AdvancedInventory\Model\Stock $aiStockModel,
        \Magento\Framework\Registry $registry,
        \Wyomind\AdvancedInventory\Helper\Assignation $aiAssigntnHlpr,
        \Wyomind\PointOfSale\Model\PointOfSale $posModel,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_aiStockModel = $aiStockModel;
        $this->_registry = $registry;
        $this->_aiAssigntnHlpr = $aiAssigntnHlpr;
        $this->_posModel = $posModel;
        $this->_storeId = $this->_storeManager->getStore()->getStoreId();
    }
    
    public function getCurrentProduct()
    {
        return $this->product = $this->_registry->registry('current_product');
    }

    public function getStoreWiseStock($product)
    {
        $_stockData = '';
        $isMultistock = false;

        if($this->_aiAssigntnHlpr->isAIEnabled())
        {
            $isMultistock = $this->_aiStockModel->isMultiStockEnabledByProductId($product->getId());
        }    
        $storeStockData = [];
        if($isMultistock)
        {
            $places = $this->_posModel->getPlacesByStoreId($this->_storeId, true);
            $placeIds = [];

            foreach ($places as $place) 
            {
                $placeIds[] = $place->getPlaceId();
            }
            
            $stocks = $this->_aiStockModel->getStockSettings($product->getId(), false, $placeIds);
            
            foreach ($places as $place) 
            {
                if($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
                {
                    if ($place->getManageInventory() == 2)
                    {
                        $warehouses = explode(',', $place->getWarehouses());

                        $stocks = $this->_aiStockModel->getStockSettings($product->getId(), false, $warehouses);

                        $warehousePlaces = $this->_posModel->getPlacesByIds($place->getWarehouses());

                        foreach ($warehousePlaces as $warehouse) 
                        {
                            $isInStock = "manage_stock_" . $warehouse->getId() . "";
                            $qty = "quantity_" . $warehouse->getId() . "";
                            $inStockLabel = 'OUT OF STOCK';

                            if($stocks[$isInStock] == 1)
                            {
                                $inStockLabel = 'IN STOCK';
                            }
                            $storeStockData[$place->getId()]['name'] = $warehouse->getName();
                            $storeStockData[$place->getId()]['is_in_stock'] = $inStockLabel;
                            $storeStockData[$place->getId()]['quantity'] = $stocks[$qty];
                        }
                    }
                    else
                    {
                        $isInStock = "is_in_stock_" . $place->getId() . ""; 
                        $qty = "quantity_" . $place->getId() . ""; 
                        $inStockLabel = 'OUT OF STOCK';

                        if($stocks[$isInStock] == 1)
                        {
                            $inStockLabel = 'IN STOCK';
                        }

                        $storeStockData[$place->getId()]['name'] = $place->getName();
                        $storeStockData[$place->getId()]['is_in_stock'] = $inStockLabel;
                        $storeStockData[$place->getId()]['quantity'] = $stocks[$qty];
                        
                    }
                }
            }
        }
        return $storeStockData;
    }    
}
