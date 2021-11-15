<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Manager;

class CouponManager implements ManagerInterface
{
    /**
     * @var \Aitoc\Gifts\Model\CouponFactory
     */
    private $couponFactory;

    /**
     * @var \Aitoc\Gifts\Model\Date
     */
    private $date;

    /**
     * @var \Aitoc\Gifts\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Aitoc\Gifts\Model\Validator\RuleValidator
     */
    private $ruleValidator;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    public function __construct(
        \Aitoc\Gifts\Model\CouponFactory $couponFactory,
        \Aitoc\Gifts\Model\Date $date,
        \Aitoc\Gifts\Model\RuleFactory $ruleFactory,
        \Aitoc\Gifts\Model\Validator\RuleValidator $ruleValidator,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->couponFactory = $couponFactory;
        $this->date = $date;
        $this->ruleFactory = $ruleFactory;
        $this->ruleValidator = $ruleValidator;
        $this->cart = $cart;
    }

    /**
     * @param string $couponCode
     *
     * @return bool
     */
    public function isCouponValid($couponCode)
    {
        $coupon = $this->getCouponByCouponCode($couponCode);

        if (!$coupon) {
            $ruleByCoupon = $this->getRuleInstance()->getRuleByCouponCode($couponCode);

            if ($ruleByCoupon->getId()) {
                $quote = $this->cart->getQuote();
                $rules[] = $ruleByCoupon;

                return $this->ruleValidator->validate($rules, $quote);
            }
        }

        return $coupon && $this->validateCouponByExpirationDate($coupon);
    }

    /**
     * @return \Aitoc\Gifts\Model\Coupon
     */
    private function getCouponInstance()
    {
        return $this->couponFactory->create();
    }

    /**
     * @return \Aitoc\Gifts\Model\Rule
     */
    private function getRuleInstance()
    {
        return $this->ruleFactory->create();
    }

    /**
     * @param \Aitoc\Gifts\Model\Coupon $coupon
     *
     * @return boolean
     */
    public function validateCouponByExpirationDate($coupon)
    {
        $currentDate = $this->date->getTimestamp();
        $expirationDate = $this->date->getTimestampFromString($coupon->getExpirationDate());

        return $expirationDate > $currentDate;
    }

    /**
     * @param $couponCode
     *
     * @return bool
     */
    public function getRuleIdByCouponCode($couponCode)
    {
        $coupon = $this->getCouponByCouponCode($couponCode);
        $ruleId  = $coupon ? $coupon->getRuleId() : false;;

        if (!$coupon) {
            $ruleByCoupon = $this->getRuleInstance()->getRuleByCouponCode($couponCode);
            $ruleId = $ruleByCoupon->getId() ? $ruleByCoupon->getRuleId() : false;
        }

        return $ruleId;
    }

    /**
     * @param \Aitoc\Gifts\Model\Coupon $couponCode
     *
     * @return \Aitoc\Gifts\Model\Coupon|boolean
     */
    public function getCouponByCouponCode($couponCode)
    {
        $coupon = $this->getCouponInstance()->getCouponByCode($couponCode);

        return $coupon->getCouponId() ? $coupon : false;
    }
}
