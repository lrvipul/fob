<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Api;

/**
 * @api
 */
interface StatisticRepositoryInterface
{
    const RULE_ID_FIELD_NAME = 'rule_id';
    const QUOTE_ID_FIELD_NAME = 'quote_id';
    const GIFT_COUNT_FIELD_NAME = 'gift_count';
    const PRODUCT_SKUS_FIELD_NAME = 'product_skus';
    const IS_GUEST_FIELD_NAME = 'is_guest';
    const CUSTOMER_ID_FIELD_NAME = 'customer_id';
    const CUSTOMER_EMAIL_FIELD_NAME = 'customer_email';
    const CREATED_AT_FIELD_NAME = 'created_at';
}
