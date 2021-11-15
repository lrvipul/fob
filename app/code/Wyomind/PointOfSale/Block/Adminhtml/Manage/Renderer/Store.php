<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block\Adminhtml\Manage\Renderer;

/**
 * Class Store
 * @package Wyomind\PointOfSale\Block\Adminhtml\Manage\Renderer
 */
class Store extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Backend\Block\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    /**
     * Renders grid column
     * @param   \Magento\Framework\Object $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $content = "";
        $content .= "<b>" . $row->getName() . ' [' . $row->getStoreCode() . ']</b><br>';
        $content .= $this->_posHelper->getStoreDescription($row);
        return $content;
    }
}