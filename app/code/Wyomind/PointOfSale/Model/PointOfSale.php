<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Model;

/**
 * Class PointOfSale
 * @package Wyomind\PointOfSale\Model
 */
class PointOfSale extends \Magento\Framework\Model\AbstractModel
{
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    /**
     *
     */
    public function _construct()
    {
        $this->_init('Wyomind\\PointOfSale\\Model\\ResourceModel\\PointOfSale');
    }
    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getPlaces()
    {
        $collection = $this->getCollection();
        return $collection;
    }
    /**
     * @param $id
     * @return mixed
     */
    public function getPlace($id)
    {
        return $this->getPlaces()->getPlace($id);
    }
    /**
     * @param $id
     * @return mixed
     */
    public function getPlaceById($id)
    {
        $collection = $this->getPlaces()->getPlace($id);
        $first = $collection->getFirstItem();
        return $first;
    }
    /**
     * @param $storeId
     * @param bool $onlyVisible
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getPlacesByStoreId($storeId, $onlyVisible = false)
    {
        $whereGroupId = null;
        //$login = $this->_session->isLoggedIn(); // always return false !?
        if (!$this->_framework->isAdmin()) {
            //if (!$login) {
            //    $whereGroupId = 0;
            //} else {
            $whereGroupId = $this->_session->getCustomerGroupId();
            //}
        }
        $collection = $this->getPlaces();
        if ($onlyVisible) {
            $collection->addFieldToFilter('status', ['status' => 1]);
            $collection->addFieldToFilter('visible_product_page', ['visible_product_page' => 1]);
        }
        $collection->setOrder('`position`', 'ASC')->getPlacesByStoreId($storeId, $whereGroupId);
        return $collection;
    }
    /**
     * @param $storeId
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getPlacesByStoreIdVisibleStoreLocator($storeId)
    {
        $whereGroupId = null;
        $login = $this->_session->isLoggedIn();
        if (!$this->_framework->isAdmin()) {
            if (!$login) {
                $whereGroupId = 0;
            } else {
                $whereGroupId = $this->_session->getCustomerGroupId();
            }
        }
        $collection = $this->getPlaces();
        $collection->addFieldToFilter('status', ['status' => 1]);
        $collection->addFieldToFilter('visible_store_locator', ['visible_store_locator' => 1]);
        $collection->setOrder('`position`', 'ASC')->getPlacesByStoreId($storeId, $whereGroupId);
        return $collection;
    }
    public function getPlacesByStoreIdVisibleCheckout($storeId)
    {
        $whereGroupId = null;
        $login = $this->_session->isLoggedIn();
        if (!$this->_framework->isAdmin()) {
            if (!$login) {
                $whereGroupId = 0;
            } else {
                $whereGroupId = $this->_session->getCustomerGroupId();
            }
        }
        $collection = $this->getPlaces();
        $collection->addFieldToFilter('status', ['status' => 1]);
        $collection->addFieldToFilter('visible_checkout', ['visible_checkout' => 1]);
        $collection->setOrder('`position`', 'ASC')->getPlacesByStoreId($storeId, $whereGroupId);
        return $collection;
    }
    /**
     * @param $ids
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getPlacesByIds($ids)
    {
        $collection = $this->getPlaces();
        $collection->addFieldToFilter('place_id', ['in' => $ids]);
        return $collection;
    }
    /**
     * @param $storeId
     * @return mixed
     */
    public function getCountries($storeId)
    {
        return $this->getCollection()->addFieldToFilter('status', ['status' => 1])->getCountries($storeId);
    }
    /**
     * @param $storeId
     * @return mixed
     */
    public function getCountriesVisibleStoreLocator($storeId)
    {
        return $this->getCollection()->addFieldToFilter('status', ['status' => 1])->addFieldToFilter('visible_store_locator', ['visible_store_locator' => 1])->getCountries($storeId);
    }
    /**
     * @param $storeId
     * @return mixed
     */
    public function getCountriesVisibleCheckout($storeId)
    {
        return $this->getCollection()->addFieldToFilter('status', ['status' => 1])->addFieldToFilter('visible_checkout', ['visible_checkout' => 1])->getCountries($storeId);
    }
    /**
     * @return mixed
     */
    public function getLastInsertedId()
    {
        $collection = $this->getCollection()->getLastInsertedId();
        return $collection->getFirstItem()->getPlaceId();
    }
}