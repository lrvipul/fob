<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Model;

/**
 * Class Attributes
 * @package Wyomind\PointOfSale\Model
 */
class Attributes extends \Magento\Framework\Model\AbstractModel
{


    /**
     *
     */
    public function _construct()
    {
        $this->_init('Wyomind\PointOfSale\Model\ResourceModel\Attributes');
    }



}
