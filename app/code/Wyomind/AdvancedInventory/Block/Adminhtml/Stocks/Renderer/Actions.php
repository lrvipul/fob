<?php

namespace Wyomind\AdvancedInventory\Block\Adminhtml\Stocks\Renderer;

class Actions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Backend\Block\Context $context, \Magento\Framework\Json\EncoderInterface $jsonEncoder, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_adminStore = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        parent::__construct($context, $jsonEncoder, $data);
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $inventory = $this->_stockModel->getStockSettings($row->getId());
        $actions = [];
        if ($inventory->getManagedAtProductLevel()) {
            if (in_array($row->getTypeId(), $this->_helperData->getProductTypes())) {
                $actions[] = ['url' => "javascript:InventoryManager.saveStocks('" . $this->getUrl('advancedinventory/stocks/save', ['id' => $row->getId(), "is_admin" => $this->_helperPermissions->hasAllPermissions(), "store_id" => $this->getRequest()->getParam('store', 0)]) . "','" . $row->getId() . "')", 'caption' => __('Save change'), 'id' => 'save'];
                if ($this->_helperPermissions->hasAllPermissions()) {
                    if ($this->_posModel->getPlaces()->count()) {
                        if ($this->getRequest()->getParam('store') == $this->_adminStore) {
                            $actions[] = ['caption' => !$inventory->getMultistockEnabled() ? __("Enable multi-stock") : __("Disable multi-stock"), 'url' => "javascript:InventoryManager.enableMultiStock('grid'," . $row->getId() . ")", 'id' => 'enable'];
                        }
                    }
                }
            }
        }
        if ($this->_helperPermissions->hasAllPermissions()) {
            $actions[] = ['url' => $this->getUrl('catalog/product/edit', ['id' => $row->getId(), "active_tab" => "advanced-inventory"]), 'caption' => __('Edit product'), 'popup' => true, 'id' => 'edit'];
        }
        $this->getColumn()->setActions($actions);
        return parent::render($row);
    }
}