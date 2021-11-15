<?php

namespace Wyomind\PickupAtStore\Plugin\Customer\CustomerData;

use Wyomind\PickupAtStore\Helper\Cookie;
use Magento\Customer\Model\Session;
use Wyomind\PointOfSale\Model\PointOfSale;
use Wyomind\PointOfSale\Model\PointOfSaleFactory;
class Customer
{
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, PointOfSaleFactory $posModelFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->posModelFactory = $posModelFactory;
    }
    public function afterGetSectionData($subject, $return)
    {
        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            $preferredStore = $customer->getPreferredStore();
            if ($preferredStore) {
                /** @var PointOfSale $posModel */
                $posModel = $this->posModelFactory->create();
                $store = $posModel->getPlace($preferredStore)->getFirstItem();
                if ($store) {
                    $data = ['id' => $preferredStore, 'name' => $store->getName()];
                    $this->cookieHelper->setCookie("preferred_store", json_encode($data));
                    $return['preferred_store'] = json_encode($data);
                }
            }
        }
        return $return;
    }
}