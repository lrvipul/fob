<?php

/**
 * Copyright Â© 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Observer;

/**
 * Class CheckoutSubmitAllAfter
 * @package Wyomind\AdvancedInventory\Observer
 */
class CheckoutSubmitAllAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesHelperData;
    /**
     * @var \Wyomind\PointOfSale\Model\PointOfSaleFactory
     */
    protected $_pointOfSaleFactory;
    /**
     * @var \Magento\Sales\Model\Order\Email\Container\OrderIdentity
     */
    protected $_identityContainer;
    /**
     * @var \Magento\Sales\Model\Order\Email\Container\Template
     */
    protected $_templateContainer;
    /**
     * @var \Magento\Sales\Model\Order\Email\SenderBuilderFactory
     */
    protected $_senderBuilderFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @var \Order\Email\Sender\OrderSender
     */
    protected $_orderSender;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Wyomind\PointOfSale\Model\PointOfSaleFactory $pointOfSaleFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_pointOfSaleFactory = $pointOfSaleFactory;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_frameworkData->getStoreConfig("advancedinventory/settings/enabled")) {
            $order = $observer->getEvent()->getOrder();
            if (!$order) {
                $orders = $observer->getEvent()->getOrders();
            } else {
                $orders = array($order);
            }
            foreach ($orders as $order) {
                // m2epro
                if ($order->getMagentoOrder() !== null) {
                    $order = $order->getMagentoOrder();
                }
                // purge cache
                $items = $order->getAllVisibleItems();
                foreach ($items as $item) {
                    $productId = $item->getProductId();
                    $this->_cacheHelper->purgeCache($productId);
                }
                $this->_modelAssignation->order = $order;
                $entityId = $order->getEntityId();
                $assignation = $this->_modelAssignation->run($entityId, $this->_frameworkData->isAdmin());
                $this->_modelAssignation->insert($entityId, $assignation);
                if (!isset($assignation['inventory']['place_ids'])) {
                    return;
                }
                $storeId = $order->getStore()->getId();
                if (!$this->salesHelperData->canSendNewOrderEmail($storeId)) {
                    return false;
                }
                if ($this->orderSender->prepareCopy($order)) {
                    $posIds = explode(",", $assignation['inventory']['place_ids']);
                    foreach ($posIds as $posId) {
                        // Get the destination email addresses to send copies to
                        $emails = explode(',', $this->_pointOfSaleFactory->create()->load($posId)->getInventoryNotification());
                        $countEmails = count($emails);
                        if ($countEmails > 0 && $order->getState() != \Magento\Sales\Model\Order::STATE_CANCELED && $emails[0] != '') {
                            try {
                                if ($countEmails) {
                                    foreach ($emails as $email) {
                                        $this->orderSender->sendCopy($email);
                                    }
                                }
                            } catch (\Exception $e) {
                                throw new \Exception($e->getMessage());
                            }
                        }
                    }
                }
            }
        }
    }
}