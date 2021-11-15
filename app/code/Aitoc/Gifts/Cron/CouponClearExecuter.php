<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Cron;

class CouponClearExecuter
{
    /**
     * @var \Aitoc\Gifts\Model\ResourceModel\Coupon\CollectionFactory
     */
    private $couponCollectionFactory;

    /**
     * @var \Aitoc\Gifts\Model\Manager\CouponManager
     */
    private $couponManager;

    /**
     * @var \Aitoc\Gifts\Model\ResourceModel\Coupon
     */
    private $couponResource;

    /**
     * CouponClearExecuter constructor.
     *
     * @param \Aitoc\Gifts\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory
     * @param \Aitoc\Gifts\Model\Manager\CouponManager                  $couponManager
     * @param \Aitoc\Gifts\Model\ResourceModel\Coupon                   $couponResource
     */
    public function __construct(
        \Aitoc\Gifts\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory,
        \Aitoc\Gifts\Model\Manager\CouponManager $couponManager,
        \Aitoc\Gifts\Model\ResourceModel\Coupon $couponResource
    ) {
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->couponManager = $couponManager;
        $this->couponResource = $couponResource;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        /** @var \Aitoc\Gifts\Model\ResourceModel\Coupon\Collection $couponCollection */
        $couponCollection = $this->getCouponCollection();

        if ($couponCollection->getSize()) {
            /** @var \Aitoc\Gifts\Model\Coupon $coupon */
            foreach ($couponCollection->load() as $coupon) {
                if (!$this->couponManager->validateCouponByExpirationDate($coupon)) {
                    $this->couponResource->delete($coupon);
                }
            }
        }

        return $this;
    }

    /**
     * @return \Aitoc\Gifts\Model\ResourceModel\Coupon\Collection
     */
    private function getCouponCollection()
    {
        return $this->couponCollectionFactory->create();
    }
}
