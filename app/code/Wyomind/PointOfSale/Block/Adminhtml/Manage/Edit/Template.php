<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block\Adminhtml\Manage\Edit;

/**
 * Class Template
 * @package Wyomind\PointOfSale\Block\Adminhtml\Manage\Edit
 */
class Template extends \Magento\Framework\View\Element\Template
{
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Framework\View\Element\Template\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    /**
     * @return \Wyomind\Framework\Helper\type
     */
    public function getGoogleApiKey()
    {
        return $this->_helper->getGoogleApiKey();
    }
}