<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\ProductLabel\Block;

class CustomProductLabels extends \Magento\Framework\View\Element\Template
{
	protected $objectManager;
    protected $_localeDate;
    protected $_storeManager;

    
    protected $_newTemplate = 'new.phtml';
    protected $_saleTemplate = 'sale.phtml';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_localeDate = $localeDate;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    
    public function saleLabel($product)
    {
        $saleLabelContent = '';

        if($this->isSaleProduct($product))
        {
           $saleLabelContent = $this->getLayout()->createBlock("Magento\Framework\View\Element\Template")->setTemplate("Magento_Catalog::product/list/labels/sale.phtml")->toHtml(); 
        }

        return $saleLabelContent;
    }

    public function newLabel($product)
    {
        $newLabelContent = '';

        if($this->isProductNew($product))
        {
           $newLabelContent = $this->getLayout()->createBlock("Magento\Framework\View\Element\Template")->setTemplate("Magento_Catalog::product/list/labels/new.phtml")->toHtml(); 
        }

        return $newLabelContent;
    }


    public function isProductNew($product)
    {
        $newsFromDate = $product->getNewsFromDate();
        $newsToDate = $product->getNewsToDate();
        if (!$newsFromDate && !$newsToDate) {
            return false;
        }

        return $this->_localeDate->isScopeDateInInterval(
            $product->getStore(),
            $newsFromDate,
            $newsToDate
        );
    }

    public function isSaleProduct($product)
    {
        $productType = $this->getProductType($product);
        $checkValidDate = false;
        $isSaleProduct = false;

        if($productType == 'simple')
        {
            $productSpecialPrice = $product->getSpecialPrice();
            $productPrice = $product->getPrice();
            if($productSpecialPrice > 0 && $productSpecialPrice < $productPrice)
            {
                $checkValidDate = true;
            }

        }
        elseif($productType == 'configurable')
        {
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
            $salePrice = array();
            foreach ($childProducts as $child)
            {
                $salePrice[] = $child->getSpecialPrice();
            }

            if($salePrice != '')
                $checkValidDate = true;
        }
        else
        {
            $checkValidDate = false;
        }

        if($checkValidDate)
        {
            $isSaleProduct = $this->checkIsSaleDateValid($product);
        }
        
        return $isSaleProduct;
    }

    public function checkIsSaleDateValid($product)
    {
        $specialFromDate = $product->getSpecialFromDate();
        $specialToDate = $product->getSpecialToDate();

        if (!$specialFromDate && !$specialToDate) {
            return false;
        }

        return $this->_localeDate->isScopeDateInInterval(
            $product->getStore(),
            $specialFromDate,
            $specialToDate
        ); 
    }

    public function getProductType($product)
    {
        return $product->getTypeId();
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }
}