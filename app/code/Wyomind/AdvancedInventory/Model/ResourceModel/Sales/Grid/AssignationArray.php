<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Model\ResourceModel\Sales\Grid;

class AssignationArray implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    public function toOptionArray()
    {
        $data = [];
        if ($this->_helperPermissions->isAllowed(0)) {
            $data[] = ["label" => __('Unassigned'), "value" => "0"];
        }
        foreach ($this->_posModel->getPlaces() as $p) {
            if ($this->_helperPermissions->isAllowed($p->getPlaceId())) {
                $data[] = ["label" => $p->getName(), "value" => $p->getPlaceId()];
            }
        }
        return $data;
    }
}