<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Observer\Quote;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CollectTotalsBefore implements ObserverInterface
{
    /**
     * @var \Aitoc\Gifts\Model\Manager\CouponManager
     */
    private $couponManager;

    /**
     * CollectTotalsBefore constructor.
     *
     * @param \Aitoc\Gifts\Model\Manager\CouponManager $couponManager
     */
    public function __construct(
        \Aitoc\Gifts\Model\Manager\CouponManager $couponManager
    ) {
        $this->couponManager = $couponManager;
    }

    /**
     * @param Observer $observer
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data|void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();
        $couponCode = $quote->getCouponCode();

        if ($couponCode && $this->couponManager->isCouponValid($couponCode)) {
            $addresses = $quote->getAllAddresses();

            /** @var \Magento\Quote\Model\Quote\Address $address */
            foreach ($addresses as $address) {
                $address->setCouponCode($couponCode);
            }

        }

        return $this;
    }
}
