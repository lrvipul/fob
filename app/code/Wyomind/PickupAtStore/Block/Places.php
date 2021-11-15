<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PickupAtStore\Block;

/**
 * Places block
 * This block allows the customer to select a store in a dropdown/map/list
 */
class Places extends \Magento\Framework\View\Element\Template
{
    const TEMPLATE = 'Wyomind_PickupAtStore::places.phtml';
    const TEMPLATE_IOSC = 'Wyomind_PickupAtStore::places_iosc.phtml';
    protected $_posCollectionFactory = null;
    protected $_posModelFactory = null;
    protected $_storeManager = null;
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, \Magento\Framework\View\Element\Template\Context $context, \Wyomind\PointOfSale\Model\PointOfSaleFactory $posModelFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
        $this->_posModelFactory = $posModelFactory;
        $this->_storeManager = $context->getStoreManager();
        if ($this->_framework->moduleIsEnabled("Onestepcheckout_Iosc")) {
            $this->setTemplate(self::TEMPLATE_IOSC);
        } else {
            $this->setTemplate(self::TEMPLATE);
        }
    }
    /**
     * Should the customer select the store in a dropdown ?
     * @param type $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function useDropdown($storeId = null)
    {
        return $this->_configHelper->getDropdown($storeId);
    }
    /**
     * Get the title of the block on the frontend
     * @param type $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTitle($storeId = null)
    {
        return $this->_configHelper->getPickupatstoreTitle($storeId);
    }
    /**
     * Should we display the description of the stores in the list (only in list mode)
     * @param type $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDisplayDescription($storeId = null)
    {
        return $this->_configHelper->getDisplayDescription($storeId);
    }
    public function getDisplayListAndGMapClass($storeId = null)
    {
        $class = $this->_configHelper->getDisplayList($storeId) ? "_list" : "";
        $class .= $this->_configHelper->getDisplayGmap($storeId) ? "_gmap" : "";
        if ($class == "") {
            $class = "_none";
        }
        return $class;
    }
    /**
     * @param null $storeId
     * @return \Wyomind\Framework\Helper\type
     */
    public function getNbStoresToDisplay($storeId = null)
    {
        return $this->_configHelper->getNbStoresToDisplay($storeId);
    }
    /**
     * Get the places available for pickup (filtered by Advanced Inventory if needed)
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPlaces()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $places = $this->_posModelFactory->create()->getPlacesByStoreIdVisibleCheckout($storeId);
        if ($this->_framework->moduleIsEnabled("Wyomind_AdvancedInventory")) {
            $places = $this->_pasHelper->getPickupPlaces($places);
        } else {
            $places = $this->_pasHelper->removeDisallowedPlaces($places);
        }
        $preferredStore = $this->_cookieManager->getCookie('preferred_store');
        if (!empty($preferredStore)) {
            $preferredStore = json_decode($preferredStore);
            $preferredStoreId = $preferredStore->id;
        } else {
            $preferredStoreId = -1;
        }
        $storesDistance = $this->_cookieManager->getCookie('pos-places');
        if (!empty($storesDistance)) {
            $storesDistance = json_decode($storesDistance);
        } else {
            $storesDistance = [];
        }
        if (!empty($storesDistance)) {
            $newPlaces = [];
            $counter = 0;
            foreach ($storesDistance as $distance) {
                foreach ($places as $place) {
                    if ($place->getId() == $distance->id) {
                        if (isset($distance->distance)) {
                            $place->setData('distance.text', $distance->distance->text);
                        }
                        if ($counter < $this->getNbStoresToDisplay() || $place->getId() == $preferredStoreId) {
                            $place->setData('displayed', true);
                        }
                        if ($place->getId() == $preferredStoreId) {
                            $place->setData('preferred.store', true);
                            array_unshift($newPlaces, $place);
                        } else {
                            $newPlaces[] = $place;
                        }
                        $counter++;
                    }
                }
            }
            return $newPlaces;
        } else {
            foreach ($places as $place) {
                $place->setData('displayed', true);
            }
        }
        return $places;
    }
}