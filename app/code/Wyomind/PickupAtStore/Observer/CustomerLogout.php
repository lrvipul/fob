<?php

/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 05/10/2020
 * Time: 15:00
 */
namespace Wyomind\PickupAtStore\Observer;

use Magento\Framework\Event\Observer;
use Wyomind\PickupAtStore\Helper\Cookie;
use Wyomind\PointOfSale\Model\PointOfSale;
use Wyomind\PointOfSale\Model\PointOfSaleFactory;
class CustomerLogout implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $this->cookieHelper->setCookie("preferred_store", "");
    }
}