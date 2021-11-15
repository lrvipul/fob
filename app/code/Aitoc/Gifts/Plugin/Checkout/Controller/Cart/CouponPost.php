<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Plugin\Checkout\Controller\Cart;

use Magento\Checkout\Controller\Cart\CouponPost as CouponPostController;

class CouponPost
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Aitoc\Gifts\Model\Processor\ActionProcessor
     */
    private $actionProcessor;

    /**
     * @var \Aitoc\Gifts\Model\Manager\CouponManager
     */
    private $couponManager;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $registry,
        \Aitoc\Gifts\Model\Processor\ActionProcessor $actionProcessor,
        \Aitoc\Gifts\Model\Manager\CouponManager $couponManager,
        \Magento\Framework\Escaper $escaper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->actionProcessor = $actionProcessor;
        $this->couponManager = $couponManager;
        $this->escaper = $escaper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param CouponPostController $subject
     * @param                      $result
     *
     * @return string
     */
    public function afterExecute(CouponPostController $subject, $result)
    {
        $request = $subject->getRequest();
        $couponCode = $request->getParam('remove') == 1 ? '' : trim($request->getParam('coupon_code'));
        $isRemove = $request->getParam('remove');
        $cartQuote = $this->cart->getQuote();

        if ($isRemove) {
            $this->actionProcessor->process($cartQuote);

            $this->checkoutSession
                ->getQuote()
                ->setCouponCode('')
                ->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();

            return $result;
        }

        if ($cartQuote->getCouponCode()) {
            $couponCode = $cartQuote->getCouponCode();
        }

        $codeLength = strlen($couponCode);
        if (!$codeLength || !$this->couponManager->isCouponValid($couponCode)) {
            return $result;
        }

        $this->clearMessages();

        $cartQuote->setData(\Aitoc\Gifts\Controller\RegistryConstants::AITOC_GIFTS_APPLIED_RULES_FIELD_NAME, '');
        $this->actionProcessor->processCoupon($cartQuote, $couponCode);

        $this->messageManager->addSuccessMessage(
            __(
                'You used coupon code "%1".',
                $this->escaper->escapeHtml($couponCode)
            )
        );

        $this->checkoutSession
            ->getQuote()
            ->setCouponCode($couponCode)
            ->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();

        return $result;
    }

    /**
     * @return $this
     */
    private function clearMessages()
    {
        $this->messageManager->getMessages(true);

        return $this;
    }
}
