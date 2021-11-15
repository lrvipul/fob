<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Model\ResourceModel\PointOfSale;

/**
 * Point of sale collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Wyomind\PointOfSale\Model\PointOfSale', 'Wyomind\PointOfSale\Model\ResourceModel\PointOfSale');
    }

    /**
     * @param $id
     * @return $this
     */
    public function getPlace($id)
    {

        $this->getSelect()->where("place_id=" . $id . "")->limit(1);
        return $this;
    }

    /**
     * @param $storeId
     * @param $whereGroupId
     * @return $this
     */
    public function getPlacesByStoreId($storeId,
                                       $whereGroupId)
    {
        $where = null;
        if ($whereGroupId !== null) {
            $where = " AND FIND_IN_SET(" . $whereGroupId . ",main_table.customer_group)";
        }
        $this->getSelect()->where("FIND_IN_SET(" . $storeId . ",main_table.store_id) " . $where)->order('position ASC');

        return $this;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function getCountries($storeId)
    {
        $this->getSelect()
            ->where("FIND_IN_SET(" . $storeId . ",main_table.store_id) ")
            ->group('main_table.country_code');
        return $this;
    }

    /**
     * @return $this
     */
    public function getLastInsertedId()
    {
        $this->getSelect()->order('place_id DESC')->limit(1);
        return $this;
    }

    /**
     * @param $urlKey
     * @return \Magento\Framework\DataObject|null
     */
    public function getByUrlKey($urlKey)
    {
        $collection = $this->addFieldToFilter("store_page_url_key", ['eq' => $urlKey])
            ->addFieldToFilter("store_page_enabled", ['eq' => 1]);
        if (count($collection)) {
            return $this->getFirstItem();
        } else {
            return null;
        }
    }

}
