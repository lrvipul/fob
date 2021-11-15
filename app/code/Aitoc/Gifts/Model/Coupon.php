<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model;

class Coupon extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Coupon constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry      $registry
     * @param ResourceModel\Coupon             $resource
     * @param ResourceModel\Coupon\Collection  $resourceCollection
     * @param array                            $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aitoc\Gifts\Model\ResourceModel\Coupon $resource,
        \Aitoc\Gifts\Model\ResourceModel\Coupon\Collection $resourceCollection,
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
     * @param $ruleId
     *
     * @return $this
     */
    public function getCouponById($couponId)
    {
        return $this->load($couponId);
    }

    /**
     * @param $code
     *
     * @return $this
     */
    public function getCouponByCode($code)
    {
        $this->_resource->load($this, $code, 'coupon_code');

        return $this;
    }
}
