<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\AdvancedInventory\Model\System\Config\Source;

class Statuses
{

    public function toOptionArray()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $orderConfig = $om->get("\Magento\Sales\Model\Order\Config");
        $alreadyProcessed = [];
        $data = [];
        foreach ($orderConfig->getStates() as $key => $state) {
            foreach ($orderConfig->getStateStatuses($key) as $key => $state) {
                if (!in_array($key, $alreadyProcessed)) {
                    $alreadyProcessed[] = $key;
                    $data[] = ['value' => $key, 'label' => is_string($state) ? $state : $state->getText()];
                }
            }
        }

        return $data;
    }

}
