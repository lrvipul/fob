<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PickupAtStore\Model\Carrier;

/**
 * Pickup At Store shipping method definition
 */
class PickupAtStore extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = \Wyomind\PickupAtStore\Helper\Config::PAS_CARRIER_CODE;
    protected $_isFixed = true;
    protected $_rateResultFactory = null;
    protected $_rateMethodFactory = null;
    protected $_posModelFactory = null;
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory, \Magento\Framework\Logger\Monolog $logger, \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory, \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory, \Wyomind\PointOfSale\Model\PointOfSaleFactory $posModelFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_posModelFactory = $posModelFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }
    public function getBlock()
    {
        return "pickupatstore";
    }
    /**
     * Collect all stores available
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return boolean
     */
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $preferredStore = $this->_cookieManager->getCookie('preferred_store');
        if (!empty($preferredStore)) {
            $preferredStore = json_decode($preferredStore);
            $preferredStoreId = $preferredStore->id;
        } else {
            $preferredStoreId = -1;
        }
        $shippingPrice = $this->getConfigData('handling_fee');
        $result = $this->_rateResultFactory->create();
        if ($shippingPrice !== false) {
            $storeId = $this->_storeManager->getStore()->getId();
            if ($this->_framework->isAdmin()) {
                $collection = $this->_posModelFactory->create()->getCollection();
            } else {
                $collection = $this->_posModelFactory->create()->getPlacesByStoreIdVisibleCheckout($storeId);
            }
            if ($this->_framework->moduleIsEnabled("Wyomind_AdvancedInventory")) {
                $collection = $this->_pasHelper->getPickupPlaces($collection);
            } else {
                $collection = $this->_pasHelper->removeDisallowedPlaces($collection);
            }
            $inCart = strpos($this->_request->getServerValue('HTTP_REFERER'), 'cart/') !== false && stripos($this->_request->getServerValue('REQUEST_URI'), 'review') === false;
            if ($inCart && $this->_configHelper->getEstimateGlobal()) {
                $posId = 0;
                $pos = null;
                if (is_array($collection)) {
                    if (isset($collection[0])) {
                        $pos = $collection[0];
                        $posId = $pos->getPlaceId();
                    }
                } else {
                    $pos = $collection->getFirstItem();
                    if ($pos) {
                        $posId = $pos->getPlaceId();
                    }
                }
                if ($pos != null) {
                    $method = $this->_rateMethodFactory->create();
                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->getConfigData('title'));
                    $method->setMethod($this->_code . "_0");
                    $method->setMethodTitle($this->getConfigData('title'));
                    if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                        $priceAndCost = '0.00';
                    } elseif ($pos['pos_handling_fee'] == 1 && $pos['pickup_fee'] !== "") {
                        $priceAndCost = $pos['pickup_fee'];
                    } else {
                        $priceAndCost = $shippingPrice;
                    }
                    $method->setPrice($priceAndCost);
                    $method->setCost($priceAndCost);
                    $result->append($method);
                }
            } else {
                $newCollection = [];
                $nbStoresToDisplay = $this->_configHelper->getNbStoresToDisplay();
                // reorder based on the distance
                $storesDistance = $this->_cookieManager->getCookie('pos-places');
                if (!empty($storesDistance)) {
                    $storesDistance = json_decode($storesDistance);
                } else {
                    $storesDistance = [];
                }
                if ($inCart && !empty($storesDistance)) {
                    $counter = 0;
                    foreach ($storesDistance as $distance) {
                        if ($counter < $nbStoresToDisplay || $nbStoresToDisplay == 0 || $distance->id == $preferredStoreId) {
                            foreach ($collection as $place) {
                                if (!is_array($place)) {
                                    $placeId = $place->getId();
                                } else {
                                    $placeId = $place['id'];
                                }
                                if ($placeId == $distance->id || $placeId == $distance->id) {
                                    $newCollection[] = $place;
                                    $counter++;
                                }
                            }
                        }
                    }
                } else {
                    $newCollection = $collection;
                }
                foreach ($newCollection as $pos) {
                    if (!is_array($pos)) {
                        $name = $pos->getName();
                        $placeId = $pos->getPlaceId();
                    } else {
                        $name = $pos['name'];
                        $placeId = $pos['place_id'];
                    }
                    $method = $this->_rateMethodFactory->create();
                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->getConfigData('title'));
                    $method->setMethod($this->_code . "_" . $placeId);
                    if ($preferredStoreId == $placeId) {
                        $method->setMethodTitle(__("*") . " " . $name);
                    } else {
                        $method->setMethodTitle($name);
                    }
                    if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                        $priceAndCost = "0.00";
                    } elseif ($pos['pos_handling_fee'] == 1 && $pos['pickup_fee'] !== "") {
                        $priceAndCost = $pos['pickup_fee'];
                    } else {
                        $priceAndCost = $shippingPrice;
                    }
                    $method->setPrice($priceAndCost);
                    $method->setCost($priceAndCost);
                    $result->append($method);
                }
                if ($this->_configHelper->getDisplayChoiceBlock($storeId) == "0") {
                    $method = $this->_rateMethodFactory->create();
                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->getConfigData('title'));
                    $method->setMethod($this->_code . "_0");
                    $method->setMethodTitle($this->getConfigData('title'));
                    if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                        $priceAndCost = '0.00';
                    } else {
                        $priceAndCost = $shippingPrice;
                    }
                    $method->setPrice($priceAndCost);
                    $method->setCost($priceAndCost);
                    $result->append($method);
                }
            }
        }
        return $result;
    }
    public function getAllowedMethods()
    {
        $result = [];
        $collection = $this->_posModelFactory->create()->getPlaces();
        foreach ($collection as $place) {
            $result[$this->_code . "_" . $place->getId()] = $this->getConfigData('title') . " " . $place->getName();
        }
        return $result;
    }
}