<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block\Adminhtml\Manage\Renderer;

/**
 * Class Customergroup
 * @package Wyomind\PointOfSale\Block\Adminhtml\Manage\Renderer
 */
class Customergroup extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Backend\Block\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    /**
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase|null|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $_customerGroup = [];
        $output = null;
        $allGroups = $this->_customerGroupModel->getCollection()->toOptionHash();
        foreach ($allGroups as $key => $allGroup) {
            $_customerGroup[$key] = $allGroup;
        }
        $selection = explode(',', $row->getCustomerGroup());
        if (in_array('-1', $selection) || count($selection) < 1) {
            return __("No Customer Group");
        } else {
            foreach ($selection as $v) {
                if (isset($_customerGroup[$v])) {
                    $output .= $_customerGroup[$v] . "<br>";
                }
            }
        }
        return $output;
    }
}