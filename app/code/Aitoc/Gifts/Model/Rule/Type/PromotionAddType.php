<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Rule\Type;

class PromotionAddType implements \Magento\Framework\Data\OptionSourceInterface
{
    const ALL_ITEMS_OPTION_VALUE = 0;
    const ONE_OF_ITEM_OPTION_VALUE = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
       return [
           [
               'label' =>  __('All Items'),
               'value' => self::ALL_ITEMS_OPTION_VALUE
           ],
           [
               'label' =>  __('One of the Item'),
               'value' => self::ONE_OF_ITEM_OPTION_VALUE
           ]
       ];
    }
}
