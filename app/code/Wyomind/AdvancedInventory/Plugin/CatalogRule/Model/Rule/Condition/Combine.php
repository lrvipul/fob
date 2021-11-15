<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Plugin\CatalogRule\Model\Rule\Condition;

/**
 * Class Combine
 * @package Wyomind\AdvancedInventory\Plugin\CatalogRule\Model\Rule\Condition
 */
class Combine
{
    protected $pointOfSale;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Wyomind\PointOfSale\Model\PointOfSaleFactory $pointOfSale)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->pointOfSale = $pointOfSale;
    }
    /**
     * @param $subject
     * @param $return
     * @return array
     */
    public function afterGetNewChildSelectOptions($subject, $return)
    {
        $pos = array();
        $places = $this->pointOfSale->create()->getPlaces();
        foreach ($places as $place) {
            $pos[] = array('value' => 'Wyomind\\AdvancedInventory\\Model\\Rule\\Condition\\Quantity|' . $place->getPlaceId(), 'label' => $place->getName());
        }
        $conditions = array_merge_recursive($return, array(array('label' => __('POS/WH Qty'), 'value' => $pos)));
        return $conditions;
    }
}