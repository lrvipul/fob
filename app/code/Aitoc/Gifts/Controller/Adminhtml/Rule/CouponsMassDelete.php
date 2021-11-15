<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Controller\Adminhtml\Rule;

class CouponsMassDelete extends \Aitoc\Gifts\Controller\Adminhtml\Rule
{
    /**
     * @var \Aitoc\Gifts\Model\ResourceModel\Coupon\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Aitoc\Gifts\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aitoc\Gifts\Model\ResourceModel\Coupon\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $ruleFactory, $registry, $resultPageFactory);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Coupons mass delete action
     *
     * @return void
     */
    public function execute()
    {
        $rule = $this->getRule();

        if (!$rule->getId()) {
            $this->_forward('noroute');
        }

        $codesIds = $this->getRequest()->getParam('ids');

        if (is_array($codesIds)) {
            $couponsCollection = $this->collectionFactory
                ->create()
                ->addCouponIdsFilter($codesIds);

            foreach ($couponsCollection as $coupon) {
                $coupon->delete();
            }
        }
    }
}
