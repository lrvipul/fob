<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Rule;

class CouponOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    const COUPON_CODE = 'coupon_code';
    const COUPON_NONE = 'none';
    const COUPON_GENERATOR = 'generator';
    const COUPON_SALES_RULE_ID = 'sales_rule_id';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::COUPON_NONE,
                'label' => __('None')
            ],
            [
                'value' => self::COUPON_CODE,
                'label' => __('Coupon Code')
            ],
            [
                'value' => self::COUPON_GENERATOR,
                'label' => __('Auto Generation')
            ],
            [
                'value' => self::COUPON_SALES_RULE_ID,
                'label' => __('Use Sales Rule ID')
            ],
        ];
    }
}
