<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Processor;

interface ProcessorInterface
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function process($quote);

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param string
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function processCoupon($quote, $couponCode);
}
