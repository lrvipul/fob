<?php

namespace Wyomind\AdvancedInventory\Block\Adminhtml\Stocks\Renderer;

class PosQty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Backend\Block\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $backorderable = null;
        $additional = null;
        $data = [];
        $inventory = $this->_stockModel->getStockSettings($row->getId(), $this->getColumn()->getPlaceId());
        if ($inventory->getManagedAtProductLevel()) {
            if (in_array($row->getTypeId(), $this->_helperData->getProductTypes())) {
                $data["backorder"] = false;
                if ($inventory->getMultistockEnabled()) {
                    if (!$inventory->getManagedAtStockLevel()) {
                        $data["manage_stock"] = false;
                        $html = __("X");
                        $backorderable = "<div class='backorderable ai-msg warning'  title='" . __('Qty not managed') . "' >" . __('Qty not managed') . "</div>";
                    } elseif ($inventory->getManagedAtStockLevel() == 1) {
                        $data["manage_stock"] = true;
                        $html = "<input type='text' class='keydown inventory_input' value='" . $this->_helperData->qtyFormat($inventory->getQuantityInStock(), $inventory->getIsQtyDecimal()) . "' / >";
                    } else {
                        $data["manage_stock"] = true;
                        $html = "<input type='text' disabled class='keydown inventory_input' value='" . $this->_helperData->qtyFormat($inventory->getQuantityInStock(), $inventory->getIsQtyDecimal()) . "' / >";
                    }
                    if ($inventory->getBackorderableAtStockLevel() && $inventory->getManagedAtStockLevel()) {
                        $data["backorder"] = true;
                        $backorderable = "<div class='backorderable ai-msg warning' title='" . __('Backorder allowed') . "' >Backorder allowed</div>";
                    }
                } else {
                    $html = __("-");
                    $data["manage_stock"] = false;
                    if ($inventory->getManagedAtStockLevel()) {
                        $data["manage_stock"] = true;
                    }
                    $data["backorder"] = false;
                    if ($inventory->getBackorderableAtStockLevel()) {
                        $data["backorder"] = true;
                        $backorderable = "<div class='backorderable ai-msg warning' style='display:none' title='" . __('Backorder allowed') . "' >" . __('Backorder allowed') . "</div>";
                    }
                }
                $additional = "<span class='data' data='" . json_encode($data) . "'></span>";
            } else {
                $html = __("-");
            }
            return "<span class='PosQty' id='PosQty_" . $row->getId() . "_" . $this->getColumn()->getPlaceId() . "'>" . $html . "</span>" . $additional . $backorderable;
        } else {
            return __("-");
        }
    }
}