<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block\Adminhtml\Manage\Renderer;

/**
 * Class Storeview
 * @package Wyomind\PointOfSale\Block\Adminhtml\Manage\Renderer
 */
class Storeview extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|null
     */
    protected $_storeManager = null;
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Backend\Block\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
        $this->_storeManager = $this->objectManager->get('Magento\\Store\\Model\\StoreManagerInterface');
    }
    /**
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $return = '';
        $storeViewIds = explode(',', $row->getStoreId());
        $websites = $this->_storeManager->getWebsites();
        if ($storeViewIds[0] == 0 || count($storeViewIds) < 1) {
            $return = __('No Store View');
            return $return;
        }
        foreach ($websites as $website) {
            if ($this->in_array($storeViewIds, $website->getStoreIds())) {
                $return .= "<div style='float:left;  padding:0 5px'><b><u>" . $website->getName() . "</u></b><br>";
                $storeGroups = $website->getGroupCollection();
                foreach ($storeGroups as $storeGroup) {
                    if ($this->in_array($storeViewIds, $storeGroup->getStoreIds())) {
                        $return .= "<b style='padding-left:5px;'>" . $storeGroup->getName() . "</b><br>";
                        $storeviews = $storeGroup->getStoreCollection();
                        foreach ($storeviews as $storeview) {
                            if (in_array($storeview->getId(), $storeViewIds)) {
                                $return .= "<span style='padding-left:10px;'>" . $storeview->getName() . "</span><br>";
                            }
                        }
                    }
                }
                $return .= '<div>';
            }
        }
        return $return;
    }
    /**
     * @param $arrayOne
     * @param $arrayTwo
     * @return bool
     */
    public function in_array($arrayOne, $arrayTwo)
    {
        foreach ($arrayOne as $value) {
            if (in_array($value, $arrayTwo)) {
                return true;
            }
        }
        return false;
    }
}