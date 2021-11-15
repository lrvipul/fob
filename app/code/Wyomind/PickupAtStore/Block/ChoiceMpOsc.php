<?php

namespace Wyomind\PickupAtStore\Block;

/**
 * Block defining the first step of the shipping step in the checkout
 * Allow to choice if the customer wants to pickup in store his order or not
 */
class ChoiceMpOsc extends \Wyomind\PickupAtStore\Block\Choice
{

    protected $_template = 'Wyomind_PickupAtStore::choice_mp_osc.phtml';

}
