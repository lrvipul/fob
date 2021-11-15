<?php

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\AdvancedInventory\Model\ResourceModel;

class Stock extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('advancedinventory_stock', 'id');
    }


    public function getItems($id, $productId = false)
    {


        $table = $this->getTable("advancedinventory_stock");
        $where = "";
        if ($productId) {
            $where = " AND product_id=$productId";
        }


        $select = $this->getConnection()->select()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->from($table, ['product_id', 'quantity_in_stock'])
            ->where("place_id = " . $id . $where);
        return $this->getConnection()->fetchPairs($select);

    }
}
