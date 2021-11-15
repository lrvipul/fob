<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\ResourceModel\Rule;

use Aitoc\Gifts\Model\Rule;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory, \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * _construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Aitoc\Gifts\Model\Rule', 'Aitoc\Gifts\Model\ResourceModel\Rule');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param $date
     *
     * @return $this
     */
    public function addDateFilter($date)
    {
        $this->getSelect()->where(
            'from_date is null or from_date <= ?',
            $date
        )->where(
            'to_date is null or to_date >= ?',
            $date
        );

        return $this;
    }

    /**
     * @param $ruleId
     *
     * @return $this
     */
    public function addRuleIdFilter($ruleId)
    {
        $this->addFieldToFilter(Rule::RULE_ID_TYPE_FIELD,
            [
                'eq' => $ruleId
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function addNonCouponFilter()
    {
        $this->addFieldToFilter(Rule::RULE_COUPON_TYPE_FIELD,
            [
                ['null' => true,],
                ['eq' => \Aitoc\Gifts\Model\Rule\CouponOptions::COUPON_NONE]
            ]
        );

        return $this;
    }

    /**
     * Filter collection to only active or inactive rules
     *
     * @param int $isActive
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        $this->addFieldToFilter('is_active', (int)$isActive ? 1 : 0);

        return $this;
    }

    /**
     * @param $storeId
     *
     * @return $this
     */
    public function addStoreFilter($storeId) {
        $this->addFieldToFilter(
            [Rule::RULE_STORES_FIELD, Rule::RULE_STORES_FIELD],
            [['eq' => 0],['finset' => $storeId]]
        );

        return $this;
    }

    /**
     * @param $groupId
     *
     * @return $this
     */
    public function addCustomerGroupFilter($groupId)     {
        $this->getSelect()->where(Rule::RULE_CUSTOMER_GROUP_FIELD .
            '="" OR ' . Rule::RULE_CUSTOMER_GROUP_FIELD . ' LIKE "%' . (int)$groupId . '%"');

        return $this;
    }

    /**
     * @return $this
     */
    public function sortByPriority()
    {
        $this->getSelect()->order(Rule::RULE_PRIORITY_FIELD . ' ASC');

        return $this;
    }


}
