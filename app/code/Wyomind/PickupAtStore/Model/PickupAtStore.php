<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PickupAtStore\Model;

/**
 * WebAPI implementation
 */
class PickupAtStore implements \Wyomind\PickupAtStore\Api\PickupAtStoreInterface
{
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    /**
     * @see \Wyomind\PickupAtStore\Api\PickupAtStoreInterface::getSalesOrderData
     */
    public function getSalesOrderData($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
            $orderData = $order->getData();
            foreach ($orderData as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    unset($orderData[$key]);
                }
            }
            return json_encode(["error" => false, "pickup_store" => $orderData['pickup_store'], "order" => $orderData]);
        } catch (\Exception $e) {
            return json_encode(["error" => true, "message" => $e->getMessage()]);
        }
    }
}