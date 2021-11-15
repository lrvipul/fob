<?php

namespace Wyomind\AdvancedInventory\Block\Adminhtml\Stocks\Renderer;

class StoreViewQty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_posModel;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Backend\Block\Context $context, \Wyomind\PointOfSale\Model\PointOfSaleFactory $posModel, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_posModel = $posModel;
        parent::__construct($context, $data);
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = null;
        $inventory = $this->_stockModel->getStockSettings($row->getId());
        if ($inventory->getManagedAtProductLevel()) {
            if (in_array($row->getTypeId(), $this->_helperData->getProductTypes())) {
                if ($this->getColumn()->getStoreId()) {
                    $places = $this->_posModel->create()->getPlacesByStoreId($this->getColumn()->getStoreId());
                } else {
                    $places = $this->_posModel->create()->getPlaces();
                }
                $html = (int) 0;
                foreach ($places as $p) {
                    if ($this->_helperPermissions->isAllowed($p->getPlaceId())) {
                        $data = $this->_stockModel->getStockByProductIdAndPlaceId($row->getId(), $p->getPlaceId());
                        $html += $data->getQuantityInStock();
                    }
                }
            } else {
                $html = __("-");
            }
            $enabled = $row->getMultistockEnabled() ? 'enabled' : 'disabled';
            return "<span class='GlobalQty' id='GlobalQty_" . $row->getId() . "' multistock='" . $enabled . "'>" . $html . "</span>";
        } else {
            return __("X");
        }
    }
}