<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Plugin\Core\Model;

class Notifications
{
    protected $_warnings = 0;
    protected $_ids = null;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Wyomind\AdvancedInventory\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        if ($this->helperPermissions->hasAllPermissions() && $this->_frameworkData->getStoreConfig("advancedinventory/settings/order_notification")) {
            $dateConfig = $this->_frameworkData->getStoreConfig("advancedinventory/settings/order_notification_from_date");
            $statuses = explode(",", $this->_frameworkData->getStoreConfig("advancedinventory/settings/disallow_assignation_status"));
            $data = $orderCollectionFactory->create()->getCountNotAssigned($dateConfig, $statuses);
            $this->_warnings = $data['count'];
            $this->_ids = $data['ids'];
        }
    }
    public function afterGetText($object, $return)
    {
        $html = null;
        if ($this->_warnings > 0 && $this->_frameworkData->getStoreConfig("advancedinventory/settings/order_notification")) {
            $this->_warnings > 1 ? $s = "s" : ($s = "");
            $style = null;
            if ($return != null) {
                $style = "padding-top:5px;margin-top:5px;border-top:1px solid gray;";
            }
            if (!$this->_session->getData("selected_ids")) {
                $url = $this->_urlInterface->getUrl('advancedinventory/sales/index', ['ids' => base64_encode($this->_ids)]);
                $message = __('Manage these orders');
            } else {
                $url = $this->_urlInterface->getUrl('advancedinventory/sales/ignore');
                $message = __('Ignore');
            }
            $html .= "<div style='{$style}'>" . $this->_warnings . " " . __(" of your order{$s} require your attention.") . " <a href='" . $url . "'>" . $message . "</a>
                  
            </div>";
            return $return . $html;
        }
        return $return;
    }
    public function afterIsDisplayed($object, $return)
    {
        return $return || $this->_warnings;
    }
}