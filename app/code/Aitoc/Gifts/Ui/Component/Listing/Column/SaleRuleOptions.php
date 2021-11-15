<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */

namespace Aitoc\Gifts\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\SalesRule\Model\Data\Rule as DataRule;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;

/**
 * Class SaleRuleOptions
 */
class SaleRuleOptions implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @param CollectionFactory $ruleCollectionFactory
     */
    public function __construct(
        CollectionFactory $ruleCollectionFactory
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['label' => __('--Please Select--'), 'value' => '']
        ];

        $ruleCollection = $this->ruleCollectionFactory->create()
            ->addFieldToFilter(DataRule::KEY_COUPON_TYPE, ['neq' => Rule::COUPON_TYPE_NO_COUPON]);

        foreach ($ruleCollection as $rule) {
            $options[] = ['label' => $rule->getName(), 'value' => $rule->getId()];
        }

        return $options;
    }
}
