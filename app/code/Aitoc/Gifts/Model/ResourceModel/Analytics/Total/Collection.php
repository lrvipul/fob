<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\ResourceModel\Analytics\Total;

use Aitoc\Gifts\Model\Rule;

class Collection extends \Aitoc\Gifts\Model\ResourceModel\Statistic\Collection
{
    /**
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->addTotalStatistics();
        return $this;
    }

    /**
     * @return $this
     */
    public function addTotalStatistics()
    {
        $this->getSelect()->reset(
            \Magento\Framework\DB\Select::COLUMNS
        )->columns(
            ['rule_id', 'rule.name']
        )->joinLeft(
                ['orders' => $this->getTable('sales_order')],
                'main_table.quote_id = orders.quote_id',
                ['orders_completed' =>'SUM(IF(orders.status = "completed", 1, 0))',
                    'orders' =>'SUM(IF(orders.entity_id IS NULL, 0, 1))']
        )->group('main_table.rule_id')
            ->order('main_table.rule_id ASC');
        return $this;
    }


    /**
     * @param int[] $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->getSelect()->joinLeft(
                ['rule' => $this->getTable('aitoc_gifts_rule')],
                'rule.rule_id = main_table.rule_id',
                []
            );
            $fields = array_fill(0, count($storeIds) + 1, 'orders.store_id');
            $values = [['eq' => '']];
            foreach ($storeIds as $storeId) {
                $values[] = ['finset' => $storeId];
            }

            $this->addFieldToFilter($fields, $values);
        }

        return $this;
    }

    /**
     * @param \DateTime|string $fromDate
     * @param \DateTime|string $toDate
     * @return $this
     */
    public function setDateRange($fromDate, $toDate)
    {
        $this->_reset();
        $this->getSelect()->where('main_table.created_at >= ?', $fromDate);
        $this->getSelect()->where('main_table.created_at <= ?', $toDate);

        return $this;
    }


    /**
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        $countSelect->reset(\Magento\Framework\DB\Select::HAVING);
        $countSelect->columns("count(DISTINCT e.entity_id)");

        return $countSelect;
    }
}
