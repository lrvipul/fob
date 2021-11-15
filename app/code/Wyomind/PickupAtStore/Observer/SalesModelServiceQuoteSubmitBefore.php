<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PickupAtStore\Observer;

/**
 * Observer to update the pickup_datetime from the quote to the order
 */
class SalesModelServiceQuoteSubmitBefore implements \Magento\Framework\Event\ObserverInterface
{

    private $attributes = [
        'pickup_datetime',
        'pickup_store'
    ];

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        $order = $observer->getEvent()->getData('order');
        $quote = $observer->getEvent()->getData('quote');

        foreach ($this->attributes as $attribute) {
            if ($quote->hasData($attribute)) {
                $order->setData($attribute, $quote->getData($attribute));
            }
        }

        return $this;
    }
}
