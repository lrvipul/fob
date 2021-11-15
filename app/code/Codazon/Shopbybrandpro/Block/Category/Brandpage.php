<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Shopbybrandpro\Block\Category;

class Brandpage extends \Magento\Framework\View\Element\Template 
{ 

    protected $_resultPageFactory; 

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory, 
        array $data = []
    ) {
        $this->_resultPageFactory  = $resultPageFactory;
        parent::__construct($context,$data);
    }

    
    
    public function getBrandsHtml()
    {
        //$resultPage = $this->_resultPageFactory->create();  
        
        $alphabetHtml  = $this->getLayout()->createBlock('Codazon\Shopbybrandpro\Block\Widget\BrandSlider')->setTemplate('Codazon_Shopbybrandpro::widget/alphabet_list.phtml');

        /*$featurBrandHtml  = $resultPage->getLayout()->createBlock('Codazon\Shopbybrandpro\Block\Widget\BrandSlider')->setTemplate('Codazon_Shopbybrandpro::brand/featured_brands.phtml');

        $brandSearchHtml = $resultPage->getLayout()->createBlock('Codazon\Shopbybrandpro\Block\Widget\BrandSearch')->setTemplate('Codazon_Shopbybrandpro::brand/brand_search.phtml');   

        $brandsHtml = $resultPage->getLayout()->createBlock('Codazon\Shopbybrandpro\Block\Brand\AllBrands')->setTemplate('Codazon_Shopbybrandpro::brand/all_brands.phtml');

        $brandsHtml->setChild('brand_search', $brandSearchHtml);
        $brandsHtml->setChild('featured_brands', $featurBrandHtml);
        $brandsHtml->setBlockHtml('brand_alphabet_list', $alphabetHtml); */

        return $alphabetHtml->toHtml();
    }
}