<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PickupAtStore\Magento\Sales\Model;

use Magento\Framework\App\ObjectManager;

/**
 * Class Order
 * @package Wyomind\PickupAtStore\Magento\Sales\Model
 */
class Order extends \Magento\Sales\Model\Order
{

    protected $place = null;

    /**
     * @return bool
     */
    public function isPickupAtStore()
    {
        return $this->getPickupStore() != null;
    }


    /**
     * @return \Magento\Framework\DataObject|null
     */
    public function getPickupAtStoreStore()
    {
        if ($this->isPickupAtStore() && !$this->place) {
            /** @var \Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\Collection $posCollection */
            $posCollection = ObjectManager::getInstance()->get("\Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\Collection");
            $this->place = $posCollection->getPlace($this->getPickupStore())->getFirstItem();
        }
        return $this->place;
    }

    /**
     * @return string
     */
    public function getPickupAtStoreStoreName()
    {
        if ($place = $this->getPickupAtStoreStore()) {
            return $place->getName();
        } else {
            return "";
        }
    }

    /**
     * @return string
     */
    public function getPickupAtStoreStoreCode()
    {
        if ($place = $this->getPickupAtStoreStore()) {
            return $place->getStoreCode();
        } else {
            return "";
        }
    }

    public function getPickupAtStoreStoreHours()
    {
        if ($place = $this->getPickupAtStoreStore()) {
            $posHelper = ObjectManager::getInstance()->get("\Wyomind\PointOfSale\Helper\Data");
            return $posHelper->getHours($place->getBusinessHours());
        } else {
            return "";
        }
    }

    public function getPickupAtStoreDatetime()
    {
        if ($place = $this->getPickupAtStoreStore()) {
            $pasHelper = ObjectManager::getInstance()->get("\Wyomind\PickupAtStore\Helper\Data");
            return $pasHelper->formatDatetime($this->getPickupDatetime());
        }
        return "";
    }

    public function getPickupAtStoreDate()
    {
        if ($place = $this->getPickupAtStoreStore()) {
            $pasHelper = ObjectManager::getInstance()->get("\Wyomind\PickupAtStore\Helper\Data");
            return $pasHelper->formatDate($this->getPickupDatetime());
        }
        return "";
    }

}