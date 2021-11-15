<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Model\ResourceModel;

/**
 * Class AttributesValues
 * @package Wyomind\PointOfSale\Model\ResourceModel
 */
class AttributesValues extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('pointofsale_attributes_values', 'value_id');
    }


}
