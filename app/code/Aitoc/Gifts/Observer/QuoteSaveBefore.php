<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Aitoc\Gifts\Controller\RegistryConstants;

class QuoteSaveBefore implements ObserverInterface
{
    /**
     * @var \Aitoc\Gifts\Model\Rule\Processor\ActionProcessor
     */
    private $actionProcessor;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    private $skipActions = ['couponPost'];

    /**
     * @var \Aitoc\Gifts\Model\Manager\CouponManager
     */
    private $couponManager;

    /**
     * QuoteSaveBefore constructor.
     *
     * @param \Aitoc\Gifts\Model\Processor\ActionProcessor $actionProcessor
     * @param \Magento\Framework\Registry                   $registry
     */
    public function __construct(
        \Aitoc\Gifts\Model\Processor\ActionProcessor $actionProcessor,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Aitoc\Gifts\Model\Manager\CouponManager $couponManager
    ) {
        $this->actionProcessor = $actionProcessor;
        $this->registry = $registry;
        $this->request = $request;
        $this->couponManager = $couponManager;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $action = $this->request->getActionName();
        /** @var \Magento\Quote\Model\Quote $quote */
        // $quote = $observer->getQuote() ? $observer->getQuote() : $observer->getQuoteAddress();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $quote = $cart->getQuote();

        if ($quote instanceof \Magento\Quote\Model\Quote\Address) {
            $quote = $quote->getQuote();
        }

        if ($this->registry->registry(RegistryConstants::AITOC_GIFTS_IS_APPLIED_RULES)
            || in_array($action, $this->skipActions)
        ) {
            return $this;
        }

        if (!$quote) {
            return $this;
        }

        $couponCode = $quote->getCouponCode();

        if ($couponCode && $this->couponManager->isCouponValid($couponCode)) {
            $this->actionProcessor->processCoupon($quote, $couponCode);
        } else {
            $this->actionProcessor->process($quote);
        }

        if (!$this->registry->registry(RegistryConstants::AITOC_GIFTS_IS_APPLIED_RULES)) {
            $this->registry->register(RegistryConstants::AITOC_GIFTS_IS_APPLIED_RULES, true);
        }

        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $quote->save();

        return $this;
    }
}
