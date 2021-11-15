<?php

namespace Wyomind\AdvancedInventory\Block\Adminhtml\Stocks\Renderer;

class StockStatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Backend\Block\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $inventory = $this->_stockModel->getStockSettings($row->getId());
        if (!in_array($row->getTypeId(), $this->_helperData->getProductTypes()) || !$inventory->getManagedAtProductLevel()) {
            return __("-");
        }
        $status = $inventory->getIsInStock();
        $disabled = $this->_framework->getStoreConfig("advancedinventory/settings/auto_update_stock_status") ? 'disabled' : '';
        $html = "<div style='text-align:center'>";
        $html .= "<select {$disabled} class='StockStatus ' id = 'StockStatus_" . $row->getId() . "' min='" . (string) $this->_helperData->qtyFormat($inventory->getMinQty(), $inventory->getIsQtyDecimal()) . "'>";
        $html .= "<option value='0' " . ($status == 0 ? "selected" : "") . ">" . __("Out of Stock") . "</option>";
        $html .= "<option value='1' " . ($status == 1 ? "selected" : "") . ">" . __("In Stock") . "</option>";
        $html .= "</div>";
        if ($inventory->getMinQty() != 0) {
            $html .= "<div class='ai-msg warning'>" . __("Min. qty: ") . $this->_helperData->qtyFormat($inventory->getMinQty(), $inventory->getIsQtyDecimal()) . "</div>";
        }
        return $html;
    }
}