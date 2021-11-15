<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PickupAtStore\Api;

/**
 * Cron Scheduler Pro API
 */
interface PickupAtStoreInterface
{
    /**
     * Get the order data (especially the store selected by the customer)
     * @param integer $orderId
     * @return string
     */
    public function getSalesOrderData($orderId);
    
}
