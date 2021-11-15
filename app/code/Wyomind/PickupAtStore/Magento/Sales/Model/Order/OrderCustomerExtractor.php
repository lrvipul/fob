<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare (strict_types=1);
namespace Wyomind\PickupAtStore\Magento\Sales\Model\Order;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\DataObject\Copy as CopyService;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory as AddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as RegionFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory as CustomerFactory;
use Magento\Quote\Api\Data\AddressInterfaceFactory as QuoteAddressFactory;
use Magento\Sales\Model\Order\Address as OrderAddress;
/**
 * Extract customer data from an order.
 */
class OrderCustomerExtractor extends \Magento\Sales\Model\Order\OrderCustomerExtractor
{
    /**
     * @var AddressFactory
     */
    protected $addressFactory;
    /**
     * @var RegionFactory
     */
    protected $regionFactory;
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var QuoteAddressFactory
     */
    protected $quoteAddressFactory;
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, AddressFactory $addressFactory, RegionFactory $regionFactory, CustomerFactory $customerFactory, QuoteAddressFactory $quoteAddressFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->customerFactory = $customerFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
    }
    /**
     * Extract customer data from order.
     *
     * @param int $orderId
     * @return CustomerInterface
     */
    public function extract(int $orderId) : CustomerInterface
    {
        $order = $this->orderRepository->get($orderId);
        //Simply return customer from DB.
        if ($order->getCustomerId()) {
            return $this->customerRepository->getById($order->getCustomerId());
        }
        //Prepare customer data from order data if customer doesn't exist yet.
        $customerData = $this->objectCopyService->copyFieldsetToTarget('order_address', 'to_customer', $order->getBillingAddress(), []);
        $processedAddressData = [];
        $customerAddresses = [];
        foreach ($order->getAddresses() as $orderAddress) {
            // if the order is a store pickup, do not import the shipping address
            if ($order->getPickupStore() && $orderAddress->getAddressType() == OrderAddress::TYPE_SHIPPING) {
                continue;
            }
            $addressData = $this->objectCopyService->copyFieldsetToTarget('order_address', 'to_customer_address', $orderAddress, []);
            $index = array_search($addressData, $processedAddressData);
            if ($index === false) {
                // create new customer address only if it is unique
                $customerAddress = $this->addressFactory->create(['data' => $addressData]);
                $customerAddress->setIsDefaultBilling(false);
                $customerAddress->setIsDefaultShipping(false);
                if (is_string($orderAddress->getRegion())) {
                    /** @var RegionInterface $region */
                    $region = $this->regionFactory->create();
                    $region->setRegion($orderAddress->getRegion());
                    $region->setRegionCode($orderAddress->getRegionCode());
                    $region->setRegionId($orderAddress->getRegionId());
                    $customerAddress->setRegion($region);
                }
                $processedAddressData[] = $addressData;
                $customerAddresses[] = $customerAddress;
                $index = count($processedAddressData) - 1;
            }
            $customerAddress = $customerAddresses[$index];
            // make sure that address type flags from equal addresses are stored in one resulted address
            if ($orderAddress->getAddressType() == OrderAddress::TYPE_BILLING) {
                $customerAddress->setIsDefaultBilling(true);
            }
            if ($orderAddress->getAddressType() == OrderAddress::TYPE_SHIPPING) {
                $customerAddress->setIsDefaultShipping(true);
            }
        }
        $customerData['addresses'] = $customerAddresses;
        return $this->customerFactory->create(['data' => $customerData]);
    }
}