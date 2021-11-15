<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block;

/**
 * Class Store
 * @package Wyomind\PointOfSale\Block
 */
class Store extends \Magento\Framework\View\Element\Template
{
    /**
     * @var null
     */
    private $store = null;
    /**
     * @var int|mixed
     */
    private $storeId = 0;
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Framework\View\Element\Template\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
        $this->storeId = $this->getRequest()->getParam('store');
        $this->store = $this->sourceModel->getPlaceById($this->storeId);
    }
    /**
     * @return mixed
     */
    public function getStoreName()
    {
        return $this->store->getName();
    }
    /**
     * @return mixed
     */
    public function getStoreLatitude()
    {
        return $this->store->getLatitude();
    }
    /**
     * @return mixed
     */
    public function getStoreLongitude()
    {
        return $this->store->getLongitude();
    }
    /**
     * @return string
     */
    public function getStoreGoogleRequest()
    {
        $fullAddress = $this->store->getAddressLine1();
        if ($this->store->getAddressLine2()) {
            $fullAddress .= "," . $this->store->getAddressLine2();
        }
        $fullAddress .= "," . $this->store->getCity();
        if ($this->store->getCountryCode()) {
            $fullAddress .= "," . $this->_countryModel->loadByCode($this->store->getCountryCode())->getName();
        }
        return $fullAddress;
    }
    /**
     * @return string
     */
    public function getStoreDescription()
    {
        $html = "<b>" . $this->store->getName() . "</b><br/><br/>";
        $html .= $this->store->getAddressLine1() . "<br/>";
        if ($this->store->getAddressLine2()) {
            $html .= $this->store->getAddressLine2() . "<br/>";
        }
        $html .= $this->store->getPostalCode() . ", ";
        $html .= $this->store->getCity() . "<br/>";
        return $html;
    }
    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getContent()
    {
        $this->store = $this->store->load($this->storeId);
        $pattern = $this->store->getStorePageContent();
        // common {{placeholders}}
        $replace = [];
        $replace['image'] = $this->_posHelper->getImage($this->store->getImage(), 150, 150, true, "float:right");
        $replace['name'] = $this->store->getName();
        $replace['code'] = $this->store->getStoreCode();
        $replace['address_1'] = $this->store->getAddressLine1();
        $replace['address_2'] = $this->store->getAddressLine2();
        $replace['zipcode'] = $this->store->getPostalCode();
        $replace['city'] = $this->store->getCity();
        if ($this->store->getState()) {
            $replace['state'] = $this->_regionModel->loadByCode($this->store->getState(), $this->store->getCountryCode())->getName();
        } else {
            $replace['state'] = null;
        }
        $replace['country'] = $this->_localLists->getCountryTranslation($this->store->getCountryCode());
        $replace['phone'] = $this->store->getMainPhone();
        $replace['email'] = $this->store->getEmail();
        $replace['description'] = $this->store->getDescription();
        $replace['hours'] = $this->_posHelper->getHours($this->store->getHours());
        $replace['days_off'] = $this->store->getDaysOff() ? __('Days off') . '<br/>' . $this->store->getDaysOff() : null;
        $replace['google_map'] = '<div id="map_canvas_pointofsale" style="min-width:50%;min-height:400px;"></div>';
        $replace['link'] = '<a target="blank" href="/' . $this->store->getStorePageUrlKey() . '.html">' . $this->store->getName() . "</a>";
        $search = ['{{image}}', '{{name}}', '{{code}}', '{{address_1}}', '{{address_2}}', '{{zipcode}}', '{{city}}', '{{state}}', '{{country}}', '{{phone}}', '{{email}}', '{{description}}', '{{hours}}', '{{days_off}}', '{{google_map}}', '{{link}}'];
        // additional attributes placeholders
        foreach ($this->_attributesCollection as $attribute) {
            if ($attribute->getType() == \Wyomind\PointOfSale\Helper\Data::TEXT || $attribute->getType() == \Wyomind\PointOfSale\Helper\Data::TEXTAREA) {
                $replace[$attribute->getCode()] = htmlentities($this->store->getData($attribute->getCode()));
            } elseif ($attribute->getType() == \Wyomind\PointOfSale\Helper\Data::WYSIWYG) {
                $replace[$attribute->getCode()] = $this->_filterProvider->getBlockFilter()->setStoreId($this->_storeManager->getStore()->getId())->filter($this->store->getData($attribute->getCode()));
            }
            $search[] = '{{' . $attribute->getCode() . '}}';
        }
        $pattern = str_replace($search, $replace, $pattern);
        // widgets/variables/blocks....
        $pattern = $this->_filterProvider->getBlockFilter()->setStoreId($this->_storeManager->getStore()->getId())->filter($pattern);
        return $pattern;
    }
}