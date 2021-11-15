<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Analytics;

class PromoFeed
{
    /**
     * @var \Aitoc\Gifts\Model\ResourceModel\Statistic\CollectionFactory
     */
    private $statisticCollectionFactory;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    public function __construct(
        \Aitoc\Gifts\Model\ResourceModel\Statistic\CollectionFactory $staticsticCollectionFactory,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->statisticCollectionFactory = $staticsticCollectionFactory;
        $this->jsonDecoder = $jsonDecoder;
    }


    public function getReportDatas($max = null)
    {
        $data = [];
        foreach ($this->getStatisticColletion() as $item) {
            $data[] = [
                'order_created' => $item->getOrderCreated(),
                'name' => $item->getName(),
                'increment_id' => $item->getIncrementId(),
                'customer' => $item->getCustomer(),
                'grand_total' => $item->getGrandTotal(),
                'rule_id' => $item->getRuleId(),
                'order_id' => $item->getOrderId(),
                'product_skus' => $item->getProductSkus(),
            ];
        }

        return $data;
    }

    /**
     * @return \Aitoc\Gifts\Model\ResourceModel\Rule\Collection
     */
    private function getStatisticColletion()
    {
        $collection = $this->statisticCollectionFactory->create();
        $collection->getSelect()
            ->join(
                ['rule' => $collection->getTable('aitoc_gifts_rule')],
                'main_table.rule_id = rule.rule_id',
                ['name' => 'rule.name']
            )
            ->join(
                ['order' => $collection->getTable('sales_order')],
                'main_table.quote_id = order.quote_id',
                [
                    'order_id' => 'order.entity_id',
                    'increment_id' =>'order.increment_id',
                    'order_created' =>'order.created_at',
                    'customer' =>'COALESCE(concat(order.customer_firstname, " ", order.customer_lastname), "Guest")',
                    'grand_total' =>'order.grand_total',
                ]
            )
            ->order('order.created_at DESC');

        foreach ($collection as $item) {
            $skus = $this->jsonDecoder->decode($item->getProductSkus());
            $skus = array_map(function($item) {
               return $item->sku;
            }, $skus);

            $item->setProductSkus(implode(',', $skus));
        }

        return $collection;
    }
}
