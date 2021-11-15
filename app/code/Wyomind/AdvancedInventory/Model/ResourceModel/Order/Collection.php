<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Model\ResourceModel\Order;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Framework\Data\Collection\EntityFactory $entityFactory, \Psr\Log\LoggerInterface $logger, \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy, \Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot, \Magento\Framework\DB\Helper $coreResourceHelper, \Magento\Framework\DB\Adapter\AdapterInterface $connection = null, \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $entitySnapshot, $coreResourceHelper, $connection, $resource);
    }
    public function requiresAssignation($item)
    {
        $unassigned = 0;
        $partiallyAssigned = 0;
        $assigned = 0;
        $assignedTo = explode(",", $item->getAssignedTo());
        // order too old
        if (in_array(-1, $assignedTo)) {
            return false;
        } else {
            if (in_array(0, $assignedTo)) {
                $items = $this->_assignation->getAssignationByOrderId($item->getEntityId())->getData();
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
                    return true;
                }
                if ($partiallyAssigned > 0) {
                    return true;
                }
            }
            if ($unassigned + $partiallyAssigned + $assigned == 0) {
                return false;
            }
        }
        return false;
    }
    public function getCountNotAssigned($dateConfig, $statuses)
    {
        $where = "";
        foreach ($statuses as $status) {
            $where .= "AND main_table.status<>'{$status}'";
        }
        $this->getSelect()->where("FIND_IN_SET(0,main_table.assigned_to) AND main_table.created_at >='" . $dateConfig . " 00:00:00'  " . $where);
        $count = 0;
        $orders = [];
        foreach ($this->getItems() as $order) {
            $requiresAssignation = $this->requiresAssignation($order);
            if ($requiresAssignation) {
                $count++;
                $orders[] = $order->getEntityId();
            }
        }
        return ["count" => $count, "ids" => implode(",", $orders)];
    }
}