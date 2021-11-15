<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Block\Adminhtml\Manage\Edit;

/**
 * Class Tabs
 * @package Wyomind\PointOfSale\Block\Adminhtml\Manage\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('pointofsale_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Point Of Sale'));
    }
}
