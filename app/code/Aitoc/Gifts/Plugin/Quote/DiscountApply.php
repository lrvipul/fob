<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Plugin\Quote;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Validator;

class DiscountApply
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Aitoc\Gifts\Model\Manager\DiscountManager
     */
    private $discountManager;

    /**
     * @var \Aitoc\Gifts\Model\Manager\ItemManager
     */
    private $itemManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Aitoc\Gifts\Model\Manager\DiscountManager $discountManager,
        \Aitoc\Gifts\Model\Manager\ItemManager $itemManager
    ) {
        $this->scopeConfig     = $scopeConfig;
        $this->discountManager = $discountManager;
        $this->itemManager     = $itemManager;
    }

    /**
     * @param Validator    $subject
     * @param \Closure     $proceed
     * @param AbstractItem $item
     *
     * @return mixed
     */
    public function aroundProcess(Validator $subject, \Closure $proceed, AbstractItem $item)
    {
        $discountData = [
            'discount_amount' => 0,
            'base_discount_amount' => 0,
        ];

        if ($this->itemManager->isGiftItem($item)) {
            $discountData = [
                'discount_amount' => (float)$item->getDiscountAmount(),
                'base_discount_amount' => (float)$item->getBaseDiscountAmount(),
            ];
        }

        $proceed($item);

        $item->setDiscountAmount($item->getDiscountAmount() + $discountData['discount_amount'])
            ->setBaseDiscountAmount($item->getBaseDiscountAmount() + $discountData['base_discount_amount']);

        return $subject;
    }
}
