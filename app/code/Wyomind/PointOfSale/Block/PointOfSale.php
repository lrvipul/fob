<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block;

/**
 * Class PointOfSale
 * @package Wyomind\PointOfSale\Block
 */
class PointOfSale extends \Magento\Framework\View\Element\Template
{
    /**
     * @var null
     */
    private $_places = null;
    /**
     * @var \Magento\Directory\Model\CurrencyFactory|null
     */
    private $_symbolFactory = null;
    /**
     * @var bool
     */
    private $_isPickupAtStore = false;
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Framework\View\Element\Template\Context $context, \Magento\Directory\Model\CurrencyFactory $symbolFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
        $this->_symbolFactory = $symbolFactory;
        $this->_isPickupAtStore = strpos($this->request->getUriString(), "pickupatstore") !== FALSE;
    }
    /**
     * @return int|\Wyomind\Framework\Helper\type
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getNbStoresToDisplay()
    {
        if ($this->getData('isPointOfSalePage') === true) {
            return 0;
        }
        return $this->_framework->getStoreConfig("pointofsale/settings/display_x_first_pos", $this->_storeManager->getStore()->getStoreId());
    }
    /**
     * @return \Wyomind\Framework\Helper\type
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDisplayDistance()
    {
        return $this->_framework->getStoreConfig("pointofsale/settings/display_distance", $this->_storeManager->getStore()->getStoreId());
    }
    /**
     * @return \Wyomind\Framework\Helper\type
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDisplayDuration()
    {
        return $this->_framework->getStoreConfig("pointofsale/settings/display_duration", $this->_storeManager->getStore()->getStoreId());
    }
    /**
     * @return \Wyomind\Framework\Helper\type
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUnitSystem()
    {
        return $this->_framework->getStoreConfig("pointofsale/settings/unit_system", $this->_storeManager->getStore()->getStoreId());
    }
    /**
     * @param $places
     */
    public function setPlaces($places)
    {
        $this->_places = $places;
    }
    /**
     * @return bool
     */
    public function isPickupAtStore()
    {
        return $this->_isPickupAtStore;
    }
    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencySymbol()
    {
        return $this->_symbolFactory->create()->load($this->_storeManager->getStore()->getCurrentCurrency()->getCode())->getCurrencySymbol();
    }
    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPointofsale()
    {
        if ($this->_places !== null) {
            $collection = $this->_places;
        } else {
            if ($this->_isPickupAtStore) {
                $collection = $this->_pointofsaleModel->getPlacesByStoreIdVisibleCheckout($this->_storeManager->getStore()->getStoreId());
            } else {
                $collection = $this->_pointofsaleModel->getPlacesByStoreIdVisibleStoreLocator($this->_storeManager->getStore()->getStoreId());
            }
            $collection->setOrder("position", "ASC");
        }
        return $collection;
    }
    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCountries()
    {
        if ($this->_isPickupAtStore) {
            $collection = $this->_pointofsaleModel->getCountriesVisibleCheckout($this->_storeManager->getStore()->getStoreId());
        } else {
            $collection = $this->_pointofsaleModel->getCountriesVisibleStoreLocator($this->_storeManager->getStore()->getStoreId());
        }
        $countries = [];
        foreach ($collection as $country) {
            if ($country->getCountryCode()) {
                $countryModel = $this->_countryModel->loadByCode($country->getCountryCode());
                $countryName = $countryModel->getName();
                $countries[] = ['code' => $country->getCountryCode(), 'name' => $countryName];
            }
        }
        return $countries;
    }
    /**
     * @param $place
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreLocatorStoreDescription($place)
    {
        $pattern = $place->getStoreLocatorDescription();
        // common {{placeholders}}
        $replace = [];
        $replace['image'] = $this->_posHelper->getImage($place->getImage(), 150, 150, true, "");
        $replace['name'] = $place->getName();
        $replace['code'] = $place->getStoreCode();
        $replace['address_1'] = $place->getAddressLine1();
        $replace['address_2'] = $place->getAddressLine2();
        $replace['zipcode'] = $place->getPostalCode();
        $replace['city'] = $place->getCity();
        if ($place->getState()) {
            $replace['state'] = $this->_regionModel->loadByCode($place->getState(), $place->getCountryCode())->getName();
        } else {
            $replace['state'] = null;
        }
        $replace['country'] = $this->_localLists->getCountryTranslation($place->getCountryCode());
        $replace['phone'] = $place->getMainPhone();
        $replace['email'] = $place->getEmail();
        $replace['description'] = $place->getDescription();
        $replace['hours'] = $this->_posHelper->getHours($place->getHours());
        $replace['days_off'] = $place->getDaysOff() ? __('Days off') . '<br/>' . $place->getDaysOff() : null;
        $replace['link'] = '<a target="blank" href="/' . $place->getStorePageUrlKey() . '.html">' . $place->getName() . "</a>";
        $search = ['{{image}}', '{{name}}', '{{code}}', '{{address_1}}', '{{address_2}}', '{{zipcode}}', '{{city}}', '{{state}}', '{{country}}', '{{phone}}', '{{email}}', '{{description}}', '{{hours}}', '{{days_off}}', '{{link}}', "
"];
        $replace['br'] = "<br/>";
        // additional attributes placeholders
        foreach ($this->_attributesCollection as $attribute) {
            if ($attribute->getType() == \Wyomind\PointOfSale\Helper\Data::TEXT || $attribute->getType() == \Wyomind\PointOfSale\Helper\Data::TEXTAREA) {
                $replace[$attribute->getCode()] = htmlentities($place->getData($attribute->getCode()));
            } elseif ($attribute->getType() == \Wyomind\PointOfSale\Helper\Data::WYSIWYG) {
                $replace[$attribute->getCode()] = $this->_filterProvider->getBlockFilter()->setStoreId($this->_storeManager->getStore()->getId())->filter($place->getData($attribute->getCode()));
            }
            $search[] = '{{' . $attribute->getCode() . '}}';
        }
        $pattern = str_replace($search, $replace, $pattern);
        // widgets/variables/blocks....
        $pattern = $this->_filterProvider->getBlockFilter()->setStoreId($this->_storeManager->getStore()->getId())->filter($pattern);
        return $pattern;
    }
    /**
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getJsonData()
    {
        $i = 0;
        $data = [];
        foreach ($this->getPointofsale() as $place) {
            $fullAddress = $place->getAddressLine1();
            if ($place->getAddressLine_2()) {
                $fullAddress .= "," . $place->getAddressLine2();
            }
            $fullAddress .= "," . $place->getCity();
            if ($place->getCountryCode()) {
                $fullAddress .= "," . $this->_countryModel->loadByCode($place->getCountryCode())->getName();
            }
            if (!$place->getGoogleRequest()) {
                $request = $fullAddress;
            } else {
                $request = $place->getGoogleRequest();
            }
            $data[] = ["id" => $place->getPlaceId(), "title" => "<h4><b>" . $place->getName() . "</b></h4>", "links" => ["directions" => "<a href=\"javascript:void(0);\" onclick=\"require(['pointofsale'], function(pointofsale) {pointofsale.getDirections(" . $i . ")});\">" . __("Get Directions") . "</a>", "showOnMap" => "<a target=\"_blank\" href=\"//maps.google.com/maps?q=" . $request . "\">" . __("Show on Google Map") . "</a>"], "name" => htmlentities($place->getName()), "lat" => $place->getLatitude(), "lng" => $place->getlongitude(), "country" => $place->getCountryCode(), "duration" => ["text" => null, "value" => null], "distance" => ["text" => null, "value" => null]];
            $i++;
        }
        return json_encode($data);
    }
    /**
     * @return null|\Wyomind\PointOfSale\Helper\Data
     */
    public function getPosHelper()
    {
        return $this->_posHelper;
    }
    /**
     * @return \Wyomind\Framework\Helper\type
     */
    public function getGoogleApiKey()
    {
        return $this->_posHelper->getGoogleApiKey();
    }
}