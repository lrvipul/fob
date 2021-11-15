<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block\Adminhtml\Attributes\Edit;

/**
 * Class GenericButton
 * @package Wyomind\PointOfSale\Block\Adminhtml\Attributes\Edit
 */
class GenericButton
{
    /**
     * Url Builder
     * @var \Magento\Framework\UrlInterface 
     */
    protected $urlBuilder;
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->urlBuilder = $this->context->getUrlBuilder();
    }
    /**
     * Return the current rule ID
     * 
     * @return int|null
     */
    public function getId()
    {
        $attribute = $this->registry->registry('attribute');
        return $attribute ? $attribute->getId() : null;
    }
    /**
     * Generate url by route and parameters
     * 
     * @param type $route
     * @param type $params
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
    /**
     * Check where button can be rendered
     *
     * @param string $name
     * @return string
     */
    public function canRender($name)
    {
        return $name;
    }
}