<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Model\ResourceModel\Attributes;

/**
 * Class Collection
 * @package Wyomind\PointOfSale\Model\ResourceModel\Attributes
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Wyomind\PointOfSale\Model\Attributes', 'Wyomind\PointOfSale\Model\ResourceModel\Attributes');
    }


    /**
     * @param $code
     * @return $this
     */
    public function getByCode($code) {
        $this->addFieldToFilter("code", ["eq" => $code]);
        return $this;
    }

}
