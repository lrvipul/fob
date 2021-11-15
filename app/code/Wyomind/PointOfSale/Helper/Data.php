<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Helper;

/**
 * Core general helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     *
     */
    const TEXTAREA = 0;
    /**
     *
     */
    const WYSIWYG = 1;
    /**
     *
     */
    const TEXT = 2;
    /**
     * @var \Magento\Framework\Image\AdapterFactory|null
     */
    protected $_imageAdapterFactory = null;
    /**
     * @var \Wyomind\PointOfSale\Model\ResourceModel\Attributes\Collection
     */
    protected $attributesCollectionFactory;
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context, \Magento\Framework\Image\AdapterFactory $imageAdapterFactory, \Wyomind\PointOfSale\Model\ResourceModel\Attributes\CollectionFactory $attributesCollectionFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context);
        $this->_imageAdapterFactory = $imageAdapterFactory;
        $this->attributesCollectionFactory = $attributesCollectionFactory;
    }
    /**
     * @param $src
     * @param int $xSize
     * @param int $ySize
     * @param bool $keepRatio
     * @param string $styles
     * @return string|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImage($src, $xSize = 150, $ySize = 150, $keepRatio = true, $styles = "")
    {
        if ($src != "") {
            $path = $this->_getMediaDir() . DIRECTORY_SEPARATOR . $src;
            if ($this->_file->fileExists($path)) {
                $part = explode("/", $src);
                $basename = array_pop($part);
                $cachePath = $this->_getMediaDir() . DIRECTORY_SEPARATOR . "stores" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $basename;
                $image = new \Magento\Framework\Image($this->_imageAdapterFactory->create(), $path);
                $image->constrainOnly(false);
                $image->keepAspectRatio($keepRatio);
                $image->setImageBackgroundColor(0xffffff);
                $image->keepTransparency(true);
                $image->resize($xSize, $ySize);
                $image->save($cachePath);
                $baseurl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA, false);
                return "<img style='" . $styles . "' src='" . $baseurl . "stores/cache/" . $basename . "'/>";
            } else {
                return;
            }
        } else {
            return;
        }
    }
    /**
     * @param $place
     * @return null|string|string[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreDescription($place)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $pattern = $this->_framework->getStoreConfig('pointofsale/settings/pattern', $storeId);
        $replace = [];
        $replace['image'] = $this->getImage($place->getImage(), 150, 150, true, "float:right");
        $replace['name'] = $place->getStoreName();
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
        $replace['hours'] = $this->getHours($place->getHours());
        $replace['days_off'] = $place->getDaysOff() ? __('Days off') . '<br/>' . $place->getDaysOff() : null;
        $replace['link'] = '<a target="blank" href="/' . $place->getStorePageUrlKey() . '.html">' . $place->getName() . "</a>";
        $search = ['{{image}}', '{{name}}', '{{code}}', '{{address_1}}', '{{address_2}}', '{{zipcode}}', '{{city}}', '{{state}}', '{{country}}', '{{phone}}', '{{email}}', '{{description}}', '{{hours}}', '{{days_off}}', '{{link}}'];
        // additional attributes placeholders
        $attributesCollection = $this->attributesCollectionFactory->create();
        foreach ($attributesCollection as $attribute) {
            if ($attribute->getType() == \Wyomind\PointOfSale\Helper\Data::TEXT || $attribute->getType() == \Wyomind\PointOfSale\Helper\Data::TEXTAREA) {
                $replace[$attribute->getCode()] = htmlentities($place->getData($attribute->getCode()));
            } elseif ($attribute->getType() == \Wyomind\PointOfSale\Helper\Data::WYSIWYG) {
                $replace[$attribute->getCode()] = $this->filterProvider->getBlockFilter()->setStoreId($this->_storeManager->getStore()->getId())->filter($place->getData($attribute->getCode()));
            }
            $search[] = '{{' . $attribute->getCode() . '}}';
        }
        return preg_replace('#(?:<br\\s*/?>\\s*?){2,}#', "<br>", nl2br(str_replace($search, $replace, $pattern)));
    }
    /**
     * @param $data
     * @return null|string
     */
    public function getHours($data)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $data = json_decode($data);
        $content = null;
        if ($data != null) {
            foreach ($data as $day => $hours) {
                $content .= __($day);
                $f = explode(':', $hours->from);
                $t = explode(':', $hours->to);
                $from = $f[0] * 60 * 60 + $f[1] * 60 + 1;
                $to = $t[0] * 60 * 60 + $t[1] * 60 + 1;
                $lfrom = 0;
                $lto = 0;
                if (isset($hours->lunch_from) && isset($hours->lunch_to)) {
                    $lf = explode(':', $hours->lunch_from);
                    $lt = explode(':', $hours->lunch_to);
                    $lfrom = $lf[0] * 60 * 60 + $lf[1] * 60 + 1;
                    $lto = $lt[0] * 60 * 60 + $lt[1] * 60 + 1;
                }
                $content .= ' ' . $this->_coreDate->gmtDate($this->_framework->getStoreConfig('pointofsale/settings/time', $storeId), $from) . ($lfrom != 0 ? ' - ' . date($this->_framework->getStoreConfig('pointofsale/settings/time', $storeId), $lfrom) : '') . ' - ' . ($lto != 0 ? date($this->_framework->getStoreConfig('pointofsale/settings/time', $storeId), $lto) . ' - ' : '') . date($this->_framework->getStoreConfig('pointofsale/settings/time', $storeId), $to) . "<br>";
            }
        }
        return $content;
    }
    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _getMediaDir()
    {
        return $this->_directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }
    /**
     * @return \Wyomind\Framework\Helper\type
     */
    public function getGoogleApiKey()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        return $this->_framework->getStoreConfig('pointofsale/settings/googleapi', $storeId);
    }
    /**
     * Get the handling fee of the shipping method
     * @return float|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHandlingFee()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        return $this->_framework->getStoreConfig('carriers/pickupatstore/handling_fee', $storeId);
    }
    /**
     * @return string
     */
    public function getGoogleMapsAPIScript()
    {
        if (!$this->_registry->registry('GoogleMapsAPILoaded')) {
            $this->_registry->register('GoogleMapsAPILoaded', true);
            return '<script type="text/javascript" type="text/javascript" src="' . '/' . '/' . 'maps.googleapis.com/maps/api/js?sensor=false&v=3&key=' . $this->getGoogleApiKey() . '"></script>';
        } else {
            return "";
        }
    }
}