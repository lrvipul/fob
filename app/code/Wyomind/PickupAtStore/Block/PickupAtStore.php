<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PickupAtStore\Block;

use Wyomind\PickupAtStore\Helper\Config as Config;
/**
 * Main Pickup At Store block
 * contains the configuration and the places where the customer can pick his order
 * 
 * @exclude_var e
 */
class PickupAtStore extends \Magento\Framework\View\Element\Template
{
    private $_posModelFactory = null;
    private $_config = [];
    private $_places = null;
    private $_symbolFactory = null;
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, \Magento\Framework\View\Element\Template\Context $context, \Wyomind\PointOfSale\Model\PointOfSaleFactory $posModelFactory, \Magento\Directory\Model\CurrencyFactory $symbolFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
        $this->_posModelFactory = $posModelFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->_config = $this->configHelper->getAll();
        if ($this->_config['time_range'] == 0) {
            $this->_config['time_range'] = 30;
        }
        $this->_symbolFactory = $symbolFactory;
    }
    /**
     * Is the customer logged ?
     * @return bool
     */
    public function isCustomerLogged()
    {
        return $this->_customerSession->isLoggedIn();
    }
    /**
     * Create the list of dates/hours available for pick up for a store
     * @param type $pos
     * @return array
     */
    public function createDates($pos)
    {
        $maxDays = $this->_config[Config::SCHEDULE_AHEAD];
        if ($pos->getPosMinimalDelay() == 1 && $pos->getMinimalDelay() !== "") {
            $minimumDelay = (int) $pos->getMinimalDelay() * 60;
        } else {
            $minimumDelay = (int) $this->_config[Config::MINIMAL_DELAY] * 60;
        }
        if ($pos->getData('available') == 2) {
            if ($pos->getPosMinimalDelayBackorder() == 1 && $pos->getMinimalDelayBackorder() !== "") {
                $minimumDelay = (int) $pos->getMinimalDelayBackorder() * 60;
            } else {
                $minimumDelay = (int) $this->_config[Config::MINIMAL_DELAY_BACKORDER] * 60;
            }
        }
        $dateFormat = $this->_config[Config::DATE_FORMAT];
        $timeFormat = $this->_config[Config::TIME_FORMAT];
        $currentTime = $this->_dateTime->gmtTimestamp() + $this->_dateTime->getGmtOffset("seconds");
        $firstTime = $currentTime + $minimumDelay;
        // si current time = Jeudi ET apres 12h => $first time
        if ($pos->getHours() == null) {
            return ["dates" => [], "hours" => []];
        }
        $availableDays = array_keys(json_decode($pos->getHours(), true));
        $availableHours = json_decode($pos->getHours(), true);
        $dates = [];
        $hours = [];
        $value = $this->_dateTime->date("Y-m-d", $firstTime);
        $minimumDay = $this->_dateTime->date("l", $firstTime);
        if (in_array($minimumDay, $availableDays)) {
            if ($value <= $this->_dateTime->date("Y-m-d", $currentTime)) {
                $dates[$value] = __("Today") . " - " . $this->_dateTime->date($dateFormat, $firstTime);
                $checkHour = true;
            } else {
                $dates[$value] = __($minimumDay) . " - " . $this->_dateTime->date($dateFormat, $firstTime);
                $checkHour = false;
            }
            $start = explode(":", $availableHours[$minimumDay]["from"]);
            $end = explode(":", $availableHours[$minimumDay]["to"]);
            $ls = 0;
            $le = 0;
            if (isset($availableHours[$minimumDay]["lunch_from"]) && isset($availableHours[$minimumDay]["lunch_to"])) {
                $lstart = explode(':', $availableHours[$minimumDay]["lunch_from"]);
                $lend = explode(':', $availableHours[$minimumDay]["lunch_to"]);
                $ls = $lstart[0] * 60 * 60 + $lstart[1] * 60;
                $le = $lend[0] * 60 * 60 + $lend[1] * 60;
            }
            $s = $start[0] * 60 * 60 + $start[1] * 60;
            $e = $end[0] * 60 * 60 + $end[1] * 60;
            while ($s < $e) {
                if (!$checkHour || $checkHour && date("H:i", $s) >= date("H:i", $firstTime)) {
                    if ($ls == 0 || ($s < $ls || $le <= $s)) {
                        $hours[$value][date("H:i", $s)] = [date($timeFormat, $s)];
                    }
                }
                $s += $this->_config["time_range"] * 60;
            }
            if (empty($hours[$value])) {
                unset($dates[$value]);
            }
        }
        $j = 1;
        while ($j < $maxDays) {
            $value = $firstTime + $j * 86400;
            $day = $this->_dateTime->date("l", $value);
            if (in_array($day, $availableDays)) {
                $indice = $this->_dateTime->date("Y-m-d", $value);
                $dates[$indice] = __($this->_dateTime->date("l", $value)) . " - " . $this->_dateTime->date($dateFormat, $value);
                $start = explode(":", $availableHours[$day]["from"]);
                $end = explode(":", $availableHours[$day]["to"]);
                $ls = 0;
                $le = 0;
                if (isset($availableHours[$day]["lunch_from"]) && isset($availableHours[$day]["lunch_to"])) {
                    $lstart = explode(":", $availableHours[$day]["lunch_from"]);
                    $lend = explode(":", $availableHours[$day]["lunch_to"]);
                    $ls = $lstart[0] * 60 * 60 + $lstart[1] * 60;
                    $le = $lend[0] * 60 * 60 + $lend[1] * 60;
                }
                $s = $start[0] * 60 * 60 + $start[1] * 60;
                $e = $end[0] * 60 * 60 + $end[1] * 60;
                while ($s < $e) {
                    if ($ls == 0 || ($s < $ls || $le <= $s)) {
                        $hours[$indice][date("H:i", $s)] = [date($timeFormat, $s)];
                    }
                    $s += $this->_config[Config::TIME_RANGE] * 60;
                }
                if (empty($hours[$indice])) {
                    unset($dates[$indice]);
                }
            }
            $j++;
        }
        // Days / hours off
        $storeDaysOff = array_map('trim', explode("
", $pos->getDaysOff()));
        foreach ($storeDaysOff as $dayOff) {
            $hoursOff = substr($dayOff, 11);
            $dayOff = substr($dayOff, 0, 10);
            if (false !== $hoursOff && in_array($dayOff, $hours)) {
                $hoursRangeToExclude = explode('-', $hoursOff);
                foreach ($hours[$dayOff] as $hourToTest => $hourOutput) {
                    if ($hourToTest >= $hoursRangeToExclude[0] && $hourToTest < $hoursRangeToExclude[1]) {
                        unset($hours[$dayOff][$hourToTest]);
                    }
                }
            } else {
                unset($dates[$dayOff]);
            }
        }
        return ['dates' => $dates, 'hours' => $hours];
    }
    /**
     * Get the configuration of the shipping method
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }
    /**
     * Get the API key for Google Map (from Point Of Sale module)
     * @return string
     */
    public function getGmapAPIKey()
    {
        return $this->_config[Config::GMAP_API_KEY];
    }
    /**
     * Get the stores available, depending of the module Advanced Inventory if needed
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPickupPlaces()
    {
        if ($this->_places == null) {
            $storeId = $this->_storeManager->getStore()->getId();
            $this->_places = $this->_posModelFactory->create()->getPlacesByStoreIdVisibleCheckout($storeId);
            if ($this->_framework->moduleIsEnabled("Wyomind_AdvancedInventory")) {
                $this->_places = $this->_pasHelper->getPickupPlaces($this->_places);
            } else {
                $this->_places = $this->_pasHelper->removeDisallowedPlaces($this->_places);
            }
        }
        return $this->_places;
    }
    /**
     * Get the dates/hours for all stores
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDateTimes()
    {
        $dateTimes = [];
        $collection = $this->getPickupPlaces();
        foreach ($collection as $pos) {
            $slots = $this->createDates($pos);
            $nbSlotsAvailable = $pos->getNbSlots();
            if ($nbSlotsAvailable > 0) {
                $orders = $this->_pasHelper->getOrders($pos->getPlaceId(), array_keys($slots['dates']));
                $slotsUnavailableDay = [];
                $slotsUnavailableTime = [];
                foreach ($orders as $order) {
                    $day = date('Y-m-d', strtotime($order['pickup_datetime']));
                    $time = date('H:i', strtotime($order['pickup_datetime']));
                    if (!isset($slotsUnavailableDay[$day])) {
                        $slotsUnavailableDay[$day] = 1;
                    } else {
                        $slotsUnavailableDay[$day]++;
                    }
                    if (!isset($slotsUnavailableTime[$day][$time])) {
                        $slotsUnavailableTime[$day][$time] = 1;
                    } else {
                        $slotsUnavailableTime[$day][$time]++;
                    }
                }
                if ($this->_config[Config::DATE] && !$this->_config[Config::TIME]) {
                    foreach ($slots['dates'] as $date => $label) {
                        if (isset($slotsUnavailableDay[$date]) && $slotsUnavailableDay[$date] >= $nbSlotsAvailable) {
                            unset($slots['dates'][$date]);
                        }
                    }
                }
                if ($this->_config[Config::DATE] && $this->_config[Config::TIME]) {
                    foreach ($slots['hours'] as $date => $hours) {
                        foreach ($hours as $hour => $label) {
                            if (isset($slotsUnavailableTime[$date][$hour]) && $slotsUnavailableTime[$date][$hour] >= $nbSlotsAvailable) {
                                unset($slots['hours'][$date][$hour]);
                            }
                        }
                    }
                    foreach ($slots['hours'] as $date => $hours) {
                        if (empty($hours)) {
                            unset($slots['dates'][$date]);
                            unset($slots['hours'][$date]);
                        }
                    }
                }
            }
            $dateTimes[$pos->getPlaceId()] = $slots;
        }
        return $dateTimes;
    }
    public function getCurrencySymbol()
    {
        return $this->_symbolFactory->create()->load($this->_storeManager->getStore()->getCurrentCurrency()->getCode())->getCurrencySymbol();
    }
    /**
     * Get javascript object shipping methods
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getShippingMethods()
    {
        $shippingMethods = [];
        $collection = $this->getPickupPlaces();
        foreach ($collection as $pos) {
            $amount = 1 == $pos->getPosHandlingFee() ? $pos->getPickupFee() : $this->_config[Config::HANDLING_FEE];
            $shippingMethods[$pos->getPlaceId()] = ["carrier_code" => "pickupatstore", "method_code" => "pickupatstore_" . $pos->getPlaceId(), "carrier_title" => $pos->getName(), "method_title" => $this->_config[Config::TITLE], "amount" => $amount, "base_amount" => $amount, "available" => true, "error_message" => "", "price_excl_tax" => 0, "price_incl_tax" => 0];
        }
        return $shippingMethods;
    }
    /**
     * Get the shipping address block title (used when pickup at store is selected in the checkout process)
     * basically replaces : "Shipping Address" with "Email Address"
     * @return string
     */
    public function getShippingAddressTitle()
    {
        return $this->_config[Config::SHIPPING_ADDRESS_TITLE];
    }
    /**
     * Get the "Ship To" block title
     * This block is the one in the review, in the second step of the checkout process
     * This block contains the shipping address for the order (for Pickup At Store, it contains the store address)
     * @return type
     */
    public function getShiptoTitle()
    {
        return $this->_config[Config::SHIPTO_TITLE];
    }
    public function getStorePickupActivatedDefault()
    {
        return $this->_config[Config::STORE_PICKUP_ACTIVATED_DEFAULT];
    }
    public function getNbStoresToDisplay()
    {
        return $this->_config[Config::NB_STORES_TO_DISPLAY];
    }
    /**
     * Get the billing address block title
     * @return type
     */
    public function getBillingAddressTitle()
    {
        return __("Billing Address");
    }
    /**
     * Get all information needed about the stores
     * @return array
     */
    public function getPlaces()
    {
        $pointsOfSale = [];
        try {
            $collection = $this->getPickupPlaces();
            foreach ($collection as $pos) {
                $region = $this->_region->loadByCode($pos->getState(), $pos->getCountryCode());
                $country = $this->_country->loadByCode($pos->getCountryCode());
                $pointsOfSale["place" . $pos->getPlaceId()] = ["lastname" => __("Store Pickup"), "email" => $pos->getEmail() ?: "no@contact.com", "firstname" => $pos->getName(), "street_1" => $pos->getAddressLine1(), "street_2" => $pos->getAddressLine2(), "city" => $pos->getCity(), "regionCode" => $pos->getState(), "regionId" => $region->getId(), "region" => $region->getDefaultName(), "postcode" => $pos->getPostalCode(), "countryId" => $pos->getCountryCode(), "country" => $country->getName(), "telephone" => $pos->getMainPhone() ?: "0000000000", "prefix" => ""];
            }
        } catch (\Exception $e) {
            $pointsOfSale = [];
        }
        // Sort the store pickups by alphabetical order
        uasort($pointsOfSale, function ($a, $b) {
            return $a['firstname'] > $b['firstname'];
        });
        return $pointsOfSale;
    }
    /**
     * Get the content of the "choice" block
     * @return string
     */
    public function getChoiceBlock($OSC = false)
    {
        if ($OSC && $OSC == "AW_OSC") {
            // Aheadworks
            return str_replace(["\r
", "\r", "
"], "", $this->getLayout()->createBlock("\\Wyomind\\PickupAtStore\\Block\\ChoiceAwOsc")->toHtml());
        } elseif ($OSC && $OSC == "MP_OSC") {
            // Mageplaza
            return str_replace(["\r
", "\r", "
"], "", $this->getLayout()->createBlock("\\Wyomind\\PickupAtStore\\Block\\ChoiceMpOsc")->toHtml());
        } elseif ($OSC && $OSC == "IOSC") {
            // IOsc
            return str_replace(["\r
", "\r", "
"], "", $this->getLayout()->createBlock("\\Wyomind\\PickupAtStore\\Block\\ChoiceIOsc")->toHtml());
        } else {
            return str_replace(["\r
", "\r", "
"], "", $this->getLayout()->createBlock("\\Wyomind\\PickupAtStore\\Block\\Choice")->toHtml());
        }
    }
    public function isAmastyOscEnabled()
    {
        return $this->_framework->moduleIsEnabled("Amasty_Checkout") && $this->_framework->getStoreConfig("amasty_checkout/general/enabled", $this->_storeManager->getStore()->getId());
    }
    public function iSwissupOscEnabled()
    {
        return $this->_framework->moduleIsEnabled("Swissup_Firecheckout") && $this->_framework->getStoreConfig("firecheckout/general/enabled", $this->_storeManager->getStore()->getId());
    }
    public function isIOscEnabled()
    {
        return $this->_framework->moduleIsEnabled("Onestepcheckout_Iosc");
    }
    /**
     * Get the "places" block
     * This block allows the customer to choose a store using a dropdown/map/list
     * @return string
     */
    public function getPlacesBlock()
    {
        return str_replace(["\r
", "\r", "
"], "", $this->getLayout()->createBlock("\\Wyomind\\PickupAtStore\\Block\\Places")->toHtml());
    }
    /**
     * Get the url to update the shipping method
     * @return string
     */
    public function getUpdateShippingMethodUrl()
    {
        return $this->getUrl("pickupatstore/update/shippingmethod");
    }
    public function getChooseYourStoreTitle()
    {
        return $this->_config[Config::PICKUPATSTORE_TITLE];
    }
}