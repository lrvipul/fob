<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Analytics;

class PromoTotals
{
    private $ruleCollectionFactory;

    public function __construct(
        \Aitoc\Gifts\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }


    public function getReportDatas($max = null)
    {
        $data = [];
        foreach ($this->getRuleColletion() as $rule) {
            $data[] = [
                'action' => $rule->getName(),
                'orders' => $rule->getOrders(),
                'orders_completed' => $rule->getOrdersCompleted(),
                'rule_id' => $rule->getId()
            ];
        }

        return $data;
    }

    /**
     * @return \Aitoc\Gifts\Model\ResourceModel\Rule\Collection
     */
    private function getRuleColletion()
    {
        $collection = $this->ruleCollectionFactory->create();
        $collection->getSelect()
            ->joinLeft(
                ['statistic' => $collection->getTable('aitoc_gifts_statistic')],
                'statistic.rule_id = main_table.rule_id',
                ['orders' =>'SUM(IF(orders.entity_id IS NULL, 0, 1))']
            )
            ->joinLeft(
                ['orders' => $collection->getTable('sales_order')],
                'statistic.quote_id = orders.quote_id',
                ['orders_completed' =>'SUM(IF(orders.status = "completed", 1, 0))']
            )
            ->group('main_table.rule_id')
            ->order('main_table.rule_id ASC');

        return $collection;
    }



}