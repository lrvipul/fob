<?php

namespace Wyomind\PickupAtStore\Plugin\Checkout\Model;

class TotalsInformationManagement
{
    /**
     * @var null|\Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\CollectionFactory
     */
    protected $posCollectionFactory = null;
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, \Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\CollectionFactory $posCollectionFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->posCollectionFactory = $posCollectionFactory;
    }
    public function aroundCalculate($subject, $proceed, $cartId, \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        // $quote = $this->cartRepository->get($cartId);
        $carrierCode = $addressInformation->getShippingMethodCode();
        if (stripos($carrierCode, "pickupatstore") !== false) {
            $storeId = str_replace("pickupatstore_", "", $carrierCode);
            $store = $this->posCollectionFactory->create()->getPlace($storeId)->getFirstItem();
            $region = $this->regionModel->loadByCode($store->getState(), $store->getCountryCode());
            $shippingData = ["customer_address_id" => "", "prefix" => "", "firstname" => __("Store Pickup"), "middlename" => "", "lastname" => $store->getName(), "suffix" => "", "company" => "", "street" => $store->getAddressLine1() . ($store->getAddressLine2() ? "
" . $store->getAddressLine2() : ''), "city" => $store->getCity(), "region" => $region->getDefaultName(), "region_id" => $region->getRegionId(), "postcode" => $store->getPostalCode(), "country_id" => $store->getCountryCode(), "telephone" => $store->getMainPhone() ?: "0000000000", "fax" => "", "email" => $store->getEmail() ?: "no@contact.com", "save_in_address_book" => false];
            $addressInformation->getAddress()->addData($shippingData);
        }
        return $proceed($cartId, $addressInformation);
    }
}