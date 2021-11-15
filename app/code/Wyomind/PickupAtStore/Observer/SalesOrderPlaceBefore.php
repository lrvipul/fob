<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PickupAtStore\Observer;

use Magento\Framework\Exception\LocalizedException;
/**
 * Observer to update the shipping description and shipping address depending of the store chosen by the customer
 */
class SalesOrderPlaceBefore implements \Magento\Framework\Event\ObserverInterface
{
    protected $_posCollectionFactory = null;
    protected $_quoteFactory = null;
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, \Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\CollectionFactory $posCollectionFactory, \Magento\Quote\Model\QuoteFactory $quoteFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_posCollectionFactory = $posCollectionFactory;
        $this->_quoteFactory = $quoteFactory;
    }
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        $shippingMethod = $order->getShippingMethod();
        if (strstr($shippingMethod, "pickupatstore") !== false) {
            $storeId = str_replace("pickupatstore_pickupatstore_", "", $shippingMethod);
            $store = $this->_posCollectionFactory->create()->getPlace($storeId)->getFirstItem();
            $nbSlotsAvailable = $store->getNbSlots();
            if ($nbSlotsAvailable != 0 && $this->_configHelper->getDate() && $this->_configHelper->getTime()) {
                $datetime = $order->getPickupDatetime();
                $sourceCode = $store->getPlaceId();
                $orders = $this->_pasHelper->getOrders($sourceCode, [date('Y-m-d', strtotime($datetime)), date('Y-m-d', strtotime($datetime))]);
                $nbSlotsUsed = 0;
                foreach ($orders as $o) {
                    if ($o['pickup_datetime'] == $datetime) {
                        $nbSlotsUsed++;
                    }
                }
                if ($nbSlotsUsed >= $nbSlotsAvailable) {
                    throw new LocalizedException(__('The time slot selected for collecting the order in store is no more available. Please select another one.'));
                }
            }
            $storeDetails = $store->getName() . ' ';
            $storeDetails .= " [ ";
            $o = 0;
            if ($store->getAddressLine1()) {
                $storeDetails .= $store->getAddressLine1();
                $o++;
            }
            if ($store->getAddressLine2()) {
                if ($o) {
                    $storeDetails .= ", ";
                }
                $storeDetails .= $store->getAddressLine2();
                $o++;
            }
            if ($store->getCity()) {
                if ($o) {
                    $storeDetails .= ", ";
                }
                $storeDetails .= $store->getCity();
                $o++;
            }
            if ($store->getState()) {
                if ($o) {
                    $storeDetails .= ", ";
                }
                $storeDetails .= $store->getState();
                $o++;
            }
            if ($store->getPostalCode()) {
                if ($o) {
                    $storeDetails .= ", ";
                }
                $storeDetails .= $store->getPostalCode() . " ";
            }
            $storeDetails .= " ]";
            $storeDetails .= "
";
            $storeDetails .= str_replace("<br>", "
", $this->_posHelper->getHours($store->getHours()));
            $quote = $this->_quoteFactory->create()->load($order->getQuoteId());
            if ($quote->getPickupDatetime()) {
                $order->setPickupDatetime($quote->getPickupDatetime());
            }
            if ($order->getPickupDatetime()) {
                if ($this->_configHelper->getTime()) {
                    $storeDetails .= "
" . __('Your pickup time: ') . $this->_pasHelper->formatDatetime($order->getPickupDatetime()) . "

";
                } elseif ($this->_configHelper->getDate()) {
                    $storeDetails .= "
" . __('Your pickup date: ') . $this->_pasHelper->formatDate($order->getPickupDatetime()) . "

";
                }
            }
            $order->setShippingDescription($storeDetails)->save();
            $region = $this->_region->loadByCode($store->getState(), $store->getCountryCode());
            $shippingData = ["prefix" => "", "firstname" => __("Store Pickup"), "middlename" => "", "lastname" => $store->getName(), "suffix" => "", "company" => "", "street" => $store->getAddressLine1() . ($store->getAddressLine2() ? "
" . $store->getAddressLine2() : ''), "city" => $store->getCity(), "region" => $region->getDefaultName(), "region_id" => $region->getRegionId(), "postcode" => $store->getPostalCode(), "country_id" => $store->getCountryCode(), "telephone" => $store->getMainPhone() ?: "0000000000", "fax" => "", "email" => $store->getEmail() ?: "no@contact.com", "save_in_address_book" => false];
            $order->setPickupStore($storeId);
            $order->getShippingAddress()->addData($shippingData)->save();
        }
    }
}