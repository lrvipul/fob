<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PickupAtStore\Helper;

/**
 * All the config from Stores > Configuration > Shipping Methods > Pickup At Store
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PAS_CARRIER_CODE = "pickupatstore";
    const XML_PATH_CARRIERS_PICKUPATSTORE = 'carriers/' . self::PAS_CARRIER_CODE . '/';
    const XML_PATH_CARRIERS_PICKUPATSTORE_BACKEND = 'carriers/' . self::PAS_CARRIER_CODE . '/backend/';
    const XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS = 'carriers/' . self::PAS_CARRIER_CODE . '/settings/';
    const XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS_LABELS = 'carriers/' . self::PAS_CARRIER_CODE . '/settings/labels/';
    const XML_PATH_CARRIERS_PICKUPATSTORE_DATE_SETTINGS = 'carriers/' . self::PAS_CARRIER_CODE . '/settings/date_settings/';
    const XML_PATH_CARRIERS_PICKUPATSTORE_TIME_SETTINGS = 'carriers/' . self::PAS_CARRIER_CODE . '/settings/time_settings/';
    const XML_PATH_POINTOFSALE_SETTINGS_GOOGLEAPI = 'pointofsale/settings/googleapi';
    const XML_PATH_POINTOFSALE_SETTINGS_NB_STORES_TO_DISPLAY = "pointofsale/settings/display_x_first_pos";
    const ACTIVE = 'active';
    const TITLE = 'title';
    const HANDLING_FEE = 'handling_fee';
    const CHOICE_TITLE = 'choice_title';
    const STORE_PICKUP_ACTIVATED_DEFAULT = 'store_pickup_activated_default';
    const SHIPPING_ADDRESS_TITLE = 'shipping_address_title';
    const PICKUPATSTORE_TITLE = 'pickupatstore_title';
    const SHIPTO_TITLE = 'shipto_title';
    const ESTIMATE_GLOBAL = 'estimate_global';
    const DISPLAY_CHOICE_BLOCK = 'display_choice_block';
    const DROPDOWN = 'dropdown';
    const DATE = 'date';
    const SCHEDULE_AHEAD = 'schedule_ahead';
    const DATE_FORMAT = 'date_format';
    const TIME = 'time';
    const TIME_RANGE = 'time_range';
    const MINIMAL_DELAY = 'minimal_delay';
    const MINIMAL_DELAY_BACKORDER = 'minimal_delay_backorder';
    const TIME_FORMAT = 'time_format';
    const GMAP = 'gmap';
    const GMAP_API_KEY = 'gmap_api_key';
    const DISPLAY_DESCRIPTION = 'display_description';
    const NB_STORES_TO_DISPLAY = 'display_x_first_pox';
    const DISPLAY_GMAP = 'display_gmap';
    const DISPLAY_LIST = 'display_list';
    const DISPLAY_ONLY_TITLE = 'display_only_title';
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context);
    }
    /**
     * @param $key
     * @param null $storeId
     * @return \Wyomind\Framework\Helper\type
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig($key, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        return $this->_framework->getStoreConfig($key, $storeId);
    }
    /**
     * Should we display the description of stores in list mode
     * @param type $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDisplayDescription($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS . self::DISPLAY_DESCRIPTION, $storeId);
    }
    public function getDisplayGmap($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS . self::DISPLAY_GMAP, $storeId);
    }
    public function getDisplayList($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS . self::DISPLAY_LIST, $storeId);
    }
    /**
     * Get the Google map API key (from Point Of Sale)
     * @param type $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getGmapAPIKey($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_POINTOFSALE_SETTINGS_GOOGLEAPI, $storeId);
    }
    /**
     * Is the shipping method active ?
     * @param type $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getActive($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE . self::ACTIVE, $storeId);
    }
    /**
     * Get the shipping method title
     * @param type $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTitle($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE . self::TITLE, $storeId);
    }
    public function getDisplayOnlyTitle($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_BACKEND . self::DISPLAY_ONLY_TITLE, $storeId);
    }
    /**
     * Get the handling fee of the shipping method
     * @param type $storeId
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHandlingFee($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE . self::HANDLING_FEE, $storeId);
    }
    /**
     * Get the title of the chosen block
     * @param type $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChoiceTitle($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS_LABELS . self::CHOICE_TITLE, $storeId);
    }
    /**
     * Is "Store pickup" selected by default in the checkout
     * @param int $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStorePickupActivatedDefault($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS . self::STORE_PICKUP_ACTIVATED_DEFAULT, $storeId);
    }
    /**
     * Get the "Ship to" block title (step 2 of the checkout process)
     * @param type $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getShiptoTitle($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS_LABELS . self::SHIPTO_TITLE, $storeId);
    }
    /**
     * Get the shipping address block title when pickup at store is selected in the checkout process
     * @param type $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getShippingAddressTitle($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS_LABELS . self::SHIPPING_ADDRESS_TITLE, $storeId);
    }
    public function getEstimateGlobal($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS . self::ESTIMATE_GLOBAL, $storeId);
    }
    public function getDisplayChoiceBlock($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS . self::DISPLAY_CHOICE_BLOCK, $storeId);
    }
    /**
     * Get the dropdown/list/map block title
     * @param type $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPickupatstoreTitle($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS_LABELS . self::PICKUPATSTORE_TITLE, $storeId);
    }
    /**
     * Do we display the stores in a dropdown ?
     * @param type $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDropdown($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS . self::DROPDOWN, $storeId);
    }
    /**
     * Is the customer allowed to choose a date ?
     * @param type $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDate($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_DATE_SETTINGS . self::DATE, $storeId);
    }
    /**
     * Allow the customer to choose a date until X days
     * @param type $storeId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getScheduleAhead($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_DATE_SETTINGS . self::SCHEDULE_AHEAD, $storeId);
    }
    /**
     * Get the date format to display
     * @param type $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDateFormat($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_DATE_SETTINGS . self::DATE_FORMAT, $storeId);
    }
    /**
     * Is the customer allowed to choose a time
     * @param type $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTime($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_TIME_SETTINGS . self::TIME, $storeId);
    }
    /**
     * The customer can get in store to pick his order every x minutes
     * @param type $storeId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTimeRange($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_TIME_SETTINGS . self::TIME_RANGE, $storeId);
    }
    /**
     * Get the minimal delay before the customer can pick his order in store
     * @param type $storeId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMinimalDelay($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_TIME_SETTINGS . self::MINIMAL_DELAY, $storeId);
    }
    /**
     * Get the minimal delay before the customer can pick his backorder in store
     * @param type $storeId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMinimalDelayBackorder($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_TIME_SETTINGS . self::MINIMAL_DELAY_BACKORDER, $storeId);
    }
    /**
     * Get the time format to display
     * @param type $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTimeFormat($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_TIME_SETTINGS . self::TIME_FORMAT, $storeId);
    }
    /**
     * Are the stores displayed in a map ?
     * @param type $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getGmap($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_CARRIERS_PICKUPATSTORE_SETTINGS . self::GMAP, $storeId);
    }
    public function getNbStoresToDisplay($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_POINTOFSALE_SETTINGS_NB_STORES_TO_DISPLAY, $storeId);
    }
    /**
     * Get all the config in an array
     * @param type $storeId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAll($storeId = null)
    {
        return [self::ACTIVE => $this->getActive($storeId), self::DATE => $this->getDate($storeId), self::DATE_FORMAT => $this->getDateFormat($storeId), self::DROPDOWN => $this->getDropdown($storeId), self::GMAP => $this->getDisplayGmap($storeId), self::HANDLING_FEE => $this->getHandlingFee($storeId), self::MINIMAL_DELAY => $this->getMinimalDelay($storeId), self::MINIMAL_DELAY_BACKORDER => $this->getMinimalDelayBackorder($storeId), self::SCHEDULE_AHEAD => $this->getScheduleAhead($storeId), self::TIME => $this->getTime($storeId), self::TIME_FORMAT => $this->getTimeFormat($storeId), self::TIME_RANGE => $this->getTimeRange($storeId), self::TITLE => $this->getTitle($storeId), self::GMAP_API_KEY => $this->getGmapAPIKey($storeId), self::DISPLAY_DESCRIPTION => $this->getDisplayDescription($storeId), self::CHOICE_TITLE => $this->getChoiceTitle($storeId), self::SHIPPING_ADDRESS_TITLE => $this->getShippingAddressTitle($storeId), self::PICKUPATSTORE_TITLE => $this->getPickupatstoreTitle($storeId), self::SHIPTO_TITLE => $this->getShiptoTitle($storeId), self::STORE_PICKUP_ACTIVATED_DEFAULT => $this->getStorePickupActivatedDefault($storeId), self::NB_STORES_TO_DISPLAY => $this->getNbStoresToDisplay($storeId), self::DISPLAY_GMAP => $this->getDisplayGmap($storeId), self::DISPLAY_LIST => $this->getDisplayList($storeId), self::DISPLAY_CHOICE_BLOCK => $this->getDisplayChoiceBlock($storeId)];
    }
}