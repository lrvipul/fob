<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Block\Adminhtml\Analytics;

class Total extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_analytics_total';
        $this->_blockGroup = 'Aitoc_Gifts';
        $this->_headerText = __('General');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
