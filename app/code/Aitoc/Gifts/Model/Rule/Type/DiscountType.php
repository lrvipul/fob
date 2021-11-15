<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Rule\Type;

class DiscountType implements \Magento\Framework\Data\OptionSourceInterface
{
    const DISCOUNT_AMOUNT_TYPE = 'discount_amount';
    const DISCOUNT_PERCENT_TYPE = 'discount_percent';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' =>  __('Discount Amount'),
                'value' => self::DISCOUNT_AMOUNT_TYPE
            ],
            [
                'label' =>  __('Discount Percent'),
                'value' => self::DISCOUNT_PERCENT_TYPE
            ]
        ];
    }
}
