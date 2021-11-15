<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Rule\Action;

interface ActionInterface
{
    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculateDiscount($rule, $item, $qty);

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Aitoc\Gifts\Model\Rule
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function actionProcess($quote, $rule);

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return  $this
     */
    public function calculateAndApplyDiscount($rule, $quote);
}
