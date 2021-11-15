<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model;

class Rule extends \Magento\SalesRule\Model\Rule
{
    const RULE_ACTIVE                          = '1';
    const RULE_INACTIVE                        = '0';

    const RULE_COUPON_TYPE_FIELD = 'coupon_type';
    const RULE_COUPON_CODE_FIELD = 'coupon_code';
    const RULE_ID_TYPE_FIELD = 'rule_id';
    const RULE_STORES_FIELD = 'stores';
    const RULE_CUSTOMER_GROUP_FIELD = 'customer_group';
    const RULE_PRIORITY_FIELD = 'priority';

    /**
     * @var \Aitoc\Gifts\Model\SalesRule
     */
    private $salesRule;

    /**
     * @var \Aitoc\Gifts\Model\SalesRuleFactory
     */
    private $salesRuleFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var Date
     */
    private $date;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegenFactory,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Aitoc\Gifts\Model\ResourceModel\Rule $resource,
        \Aitoc\Gifts\Model\ResourceModel\Rule\Collection $resourceCollection,
        \Aitoc\Gifts\Model\SalesRuleFactory $salesRuleFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Aitoc\Gifts\Model\Date $date,
        array $data = []
    ) {
        $this->salesRuleFactory = $salesRuleFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $couponFactory,
            $codegenFactory,
            $condCombineFactory,
            $condProdCombineF,
            $couponCollection,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
        $this->request = $request;
        $this->date = $date;
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('Aitoc\Gifts\Model\ResourceModel\Rule');
    }

    /**
     * @return mixed
     */
    public function getSalesRule()
    {
        if (!$this->salesRule) {
            $this->salesRule = $this->salesRuleFactory->create()->load($this->getId());
        }

        return $this->salesRule;
    }

    /**
     * @param $ruleId
     *
     * @return $this
     */
    public function getRuleById($ruleId)
    {
        $resource = $this->getResource();
        $resource->load($this, $ruleId);

        return $this;
    }

    /**
     * @param $couponCode
     *
     * @return $this
     */
    public function getRuleByCouponCode($couponCode)
    {
        $resource = $this->getResource();
        $resource->load($this, $couponCode, self::RULE_COUPON_CODE_FIELD);

        return $this;
    }

    /**
     * @return array
     */
    public function getPoductSkus()
    {
        return $this->getData('product_skus') ? explode(',', $this->getData('product_skus')) : [];
    }

    public function beforeSave()
    {
        parent::beforeSave();

        return $this->updateDate();
    }

    /**
     * @return $this
     */
    private function updateDate()
    {
        $toDate = $this->request->getParam('to_date');
        $fromDate = $this->request->getParam('from_date');

        $this->setToDate($toDate ? $this->date->getDateFromString($toDate, false) : null);
        $this->setFromDate($fromDate ? $this->date->getDateFromString($fromDate, false) : null);

        return $this;
    }
}
