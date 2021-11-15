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
class CustomerLogin implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var PointOfSaleFactory
     */
    protected $posModelFactory;
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, PointOfSaleFactory $posModelFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->posModelFactory = $posModelFactory;
    }
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $preferredStore = $customer->getPreferredStore();
        if ($preferredStore) {
            /** @var PointOfSale $posModel */
            $posModel = $this->posModelFactory->create();
            $store = $posModel->getPlace($preferredStore)->getFirstItem();
            if ($store) {
                $data = ['id' => $preferredStore, 'name' => $store->getName()];
                $this->cookieHelper->setCookie("preferred_store", json_encode($data));
            }
        } else {
            //$this->cookieHelper->setCookie("preferred_store", "");
            $preferredStore = $_COOKIE["preferred_store"] ?? "[]";
            if ($preferredStore) {
                $preferredStore = json_decode($preferredStore, true);
                $storeId = $preferredStore['id'] ?? -1;
                /** @var PointOfSale $posModel */
                $posModel = $this->posModelFactory->create();
                $store = $posModel->getPlace($storeId)->getFirstItem();
                if ($store) {
                    $customer->setPreferredStore($storeId);
                    $customer->save();
                } else {
                    $this->cookieHelper->setCookie("preferred_store", "");
                }
            } else {
                $this->cookieHelper->setCookie("preferred_store", "");
            }
        }
    }
}