<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Model\Config\Source;

/**
 * Class UnitSystem
 * @package Wyomind\PointOfSale\Model\Config\Source
 */
class UnitSystem implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => 'Metric', 'value' => 0],
            ['label' => 'Imperial', 'value' => 1]
        ];
    }
}

