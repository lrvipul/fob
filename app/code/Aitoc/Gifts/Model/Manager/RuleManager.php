<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Manager;

class RuleManager implements ManagerInterface
{
    /**
     * @var \Aitoc\Gifts\Model\Rule array
     */
    private $rules = [];

    /**
     * @var \Aitoc\Gifts\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var \Aitoc\Gifts\Model\Date
     */
    private $date;

    /**
     * @var \Aitoc\Gifts\Model\Validator\RuleValidator
     */
    private $ruleValidator;

    /**
     * @var CouponManager
     */
    private $couponManager;

    public function __construct(
        \Aitoc\Gifts\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Aitoc\Gifts\Model\Date $date,
        \Aitoc\Gifts\Model\Validator\RuleValidator $ruleValidator,
        \Aitoc\Gifts\Model\Manager\CouponManager $couponManager
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->date = $date;
        $this->ruleValidator = $ruleValidator;
        $this->couponManager = $couponManager;
    }

    /**
     * @param $quote
     *
     * @return \Aitoc\Gifts\Model\Rule array
     */
    public function getAvailableRules($quote)
    {
        /** @var \Aitoc\Gifts\Model\ResourceModel\Rule\Collection $rules */
        $rules = $this->getRulesWithCommonFilters($quote)->addNonCouponFilter();

        return $this->ruleValidator->validate($rules->getItems(), $quote);
    }

    /**
     * @param $quote
     * @param string $couponCode
     *
     * @return \Aitoc\Gifts\Model\Rule array
     */
    public function getAvailableRuleByCoupon($quote, $couponCode)
    {
        /** @var \Aitoc\Gifts\Model\ResourceModel\Rule\Collection $rules */
        $ruleId = $this->couponManager->getRuleIdByCouponCode($couponCode);
        $rules = [];

        if ($ruleId) {
            $rules = $this->getRulesWithCommonFilters($quote)->addRuleIdFilter($ruleId);
        }


        return $rules ? $this->ruleValidator->validate($rules->getItems(), $quote) : [];
    }

    /**
     * @param $quote
     *
     * @return \Aitoc\Gifts\Model\ResourceModel\Rule\Collection
     */
    private function getRulesWithCommonFilters($quote)
    {
        $customerGroupId = $quote->getCustomerGroupId();
        $storeId = $quote->getStoreId();

        return $this->getRulesCollection()
            ->addIsActiveFilter()
            ->addDateFilter($this->date->getCurrentDate(false))
            ->addStoreFilter($storeId)
            ->addCustomerGroupFilter($customerGroupId)
            ->sortByPriority();
    }

    /**
     * @return \Aitoc\Gifts\Model\Rule
     */
    public function getRules()
    {
        if ($this->rules) {
            return $this->rules;
        }

        $this->rules = $this->getRulesCollection()->getItems();

        return $this->rules;
    }

    /**
     * @return mixed
     */
    private function getRulesCollection()
    {
        return $this->ruleCollectionFactory->create();
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return mixed
     */
    public function getRulePromotionalItemsAddAlgorithm($rule)
    {
        return $rule->getType();
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param $multiply
     *
     * @return int
     */
    public function getQtyToAdd($rule, $multiply = null)
    {
        $discountQty = (int)$rule->getDiscountQty();

        return $multiply ? $discountQty * $multiply : $discountQty;
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return int
     */
    public function getDiscountType($rule)
    {
        return $rule->getDiscountType();
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return int
     */
    public function getDiscountAmount($rule)
    {
        return $rule->getDiscountAmount();
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return int
     */
    public function getDiscountPercent($rule)
    {
        return $rule->getDiscountPercent();
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return int
     */
    public function getDiscountStep($rule)
    {
        return (int)$rule->getDiscountStep();
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return int
     */
    public function getCartAmount($rule)
    {
        return (float)$rule->getCartAmount();
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return bool
     */
    public function validateByActionsItem($rule, $item)
    {
        /** @var \Aitoc\Gifts\Model\SalesRule $salesRule */
        $salesRule = $rule->getSalesRule();

        return $salesRule->validateActions($item);
    }
}
