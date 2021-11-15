<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\ResourceModel\Coupon;

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
        $this->_init('Aitoc\Gifts\Model\Coupon', 'Aitoc\Gifts\Model\ResourceModel\Coupon');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param $rule
     *
     * @return $this
     */
    public function addRuleToFilter($rule)
    {
        $ruleId = $rule->getId();
        $this->addFieldToFilter('rule_id', $ruleId);

        return $this;
    }

    /**
     * @param $couponIds
     *
     * @return $this
     */
    public function addCouponIdsFilter($couponIds)
    {
        $this->addFieldToFilter(
            'coupon_id',
            ['in' => $couponIds]
        );

        return $this;
    }
}
