<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model;

class Statistic extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Statistic constructor.
     *
     * @param \Magento\Framework\Model\Context   $context
     * @param \Magento\Framework\Registry        $registry
     * @param ResourceModel\Statistic            $resource
     * @param ResourceModel\Statistic\Collection $resourceCollection
     * @param array                              $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aitoc\Gifts\Model\ResourceModel\Statistic $resource,
        \Aitoc\Gifts\Model\ResourceModel\Statistic\Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }


    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('Aitoc\Gifts\Model\ResourceModel\Coupon');
    }

    /**
     * @param $statisticId
     *
     * @return $this
     */
    public function getStatisticById($statisticId)
    {
        $resource = $this->getResource();

        return $this->load($statisticId);
    }
}
