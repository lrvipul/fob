<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Block\Adminhtml\Assignation;

/**
 * Report block
 */
class View extends \Magento\Backend\Block\Template
{
    public $order = null;
    public $orderId = null;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Magento\Sales\Model\OrderFactory $orderFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
        $orderId = $this->getRequest()->getParam("order_id");
        $this->setOrder($orderFactory->create()->load($orderId));
        $this->setOrderId($orderId);
    }
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }
    public function setOrder($order)
    {
        $this->order = $order;
    }
    public function getEnableAssignation()
    {
        return $this->getRequest()->getParam('assign') == "1";
    }
}