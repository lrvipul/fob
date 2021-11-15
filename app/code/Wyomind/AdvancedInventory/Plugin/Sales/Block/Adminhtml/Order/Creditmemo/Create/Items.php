<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\AdvancedInventory\Plugin\Sales\Block\Adminhtml\Order\Creditmemo\Create;

class Items
{

    public function aroundGetItemRenderer(
        $subject,
        $proceed,
        $type
    ) {

        return $proceed("advancedinventory");
    }
}
