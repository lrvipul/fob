<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PickupAtStore\Block;

/**
 * Block defining the first step of the shipping step in the checkout
 * Allow to choice if the customer wants to pickup in store his order or not
 */
class ChoiceAwOsc extends \Wyomind\PickupAtStore\Block\Choice
{

    protected $_template = 'Wyomind_PickupAtStore::choice_aw_osc.phtml';

}
