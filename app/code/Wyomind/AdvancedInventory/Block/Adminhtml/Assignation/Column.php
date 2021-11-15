<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Block\Adminhtml\Assignation;

/**
 * Report block
 */
class Column extends \Magento\Backend\Block\Template
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    public function _toHtml()
    {
        $orderId = $this->getRequest()->getParam("entity_id");
        $item = (array) $this->_modelOrder->load($orderId)->getData();
        return $this->getAssignation($item);
    }
    public function getAssignation($item)
    {
        $unassigned = 0;
        $partiallyAssigned = 0;
        $assigned = 0;
        if (isset($item['order_status'])) {
            $entityId = $item['order_id'];
            $status = $item['order_status'];
            $enableAssignation = "0";
        } else {
            $entityId = $item['entity_id'];
            if (isset($item['status'])) {
                $status = $item['status'];
            } else {
                $status = "";
            }
            $enableAssignation = "1";
        }
        $onclick = "InventoryManager.viewAssignation(this,\"" . $this->_urlBuilder->getUrl('advancedinventory/assignation/view', ["order_id" => $entityId, "assign" => $enableAssignation]) . "\")";
        $assignedTo = explode(",", $item["assigned_to"]);
        $value = "<div id='assignation_column_" . $entityId . "'>";
        // order too old
        if (in_array(-1, $assignedTo)) {
            $value .= "<div style='color:grey;'>" . __("Order placed before multistock initialization") . "</div>";
        } else {
            if (in_array(0, $assignedTo)) {
                $items = $this->_assignation->getAssignationByOrderId($entityId)->getData();
                foreach ($items as $i) {
                    if ($i['multistock_enabled']) {
                        if ($i['qty_assigned'] == 0 && $i["qty_unassigned"] > 0) {
                            $unassigned++;
                        } elseif ($i['qty_unassigned'] > 0) {
                            $partiallyAssigned++;
                        }
                    }
                }
                if ($unassigned > 0) {
                    $color = $this->_helperAssignation->isUpdatable($status) ? "red" : "grey";
                    $value .= "<a style='color:{$color};' href='javascript:void(0)' onclick='" . $onclick . "'>" . $unassigned . " item" . ($unassigned < 2 ? null : "s") . ($unassigned < 2 ? " is " : " are ") . __("unassigned") . "</a><br/>";
                }
                if ($partiallyAssigned > 0) {
                    $color = $this->_helperAssignation->isUpdatable($status) ? "orange" : "grey";
                    $value .= "<a style='color:{$color};'  href='javascript:void(0)' onclick='" . $onclick . "'>" . $partiallyAssigned . " item" . ($partiallyAssigned < 2 ? null : "s") . ($partiallyAssigned < 2 ? " is " : " are ") . __("partially unassigned") . "</a><br/>";
                }
            }
            // assigned to
            $color = $this->_helperAssignation->isUpdatable($status) ? "green" : "grey";
            foreach ($assignedTo as $id) {
                if ($id > 0) {
                    $assigned++;
                    $value .= "<a style='color:{$color};'  href='javascript:void(0)' onclick='" . $onclick . "'>" . $this->_posModel->load($id)->getName() . "</a><br/>";
                }
            }
            if ($unassigned + $partiallyAssigned + $assigned == 0) {
                $value .= "<div style='color:black;'>" . __("No assignation required") . "</div>";
            }
        }
        return $value . "</div>";
    }
}