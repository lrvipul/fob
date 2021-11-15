<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Plugin\Checkout\Block\Cart;

use Magento\Framework\View\Element\AbstractBlock as Block;

class ProductName
{
    const AITOC_FREE_GIFT_NS = 'ait_free_gift';

    /**
     * @param Block $subject
     * @param \Closure $proceed
     * @param $data
     * @param null $allowedTags
     * @return mixed
     */
    public function aroundEscapeHtml(Block $subject, \Closure $proceed, $data, $allowedTags = null)
    {
        if (is_string($data) && strpos($data, self::AITOC_FREE_GIFT_NS) !== false) {
            return str_replace(self::AITOC_FREE_GIFT_NS, '', $data);
        }

        return $proceed($data, $allowedTags);
    }
}
