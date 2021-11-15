<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Manager;

use Magento\SalesRule\Model\Rule\Action\Discount\Data;

class DiscountManager implements ManagerInterface
{
    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var RuleManager
     */
    private $ruleManager;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory
     */
    private $discountDataFactory;

    /**
     * DiscountManager constructor.
     *
     * @param ItemManager                                               $itemManager
     * @param \Magento\Store\Model\Store                                $store
     * @param RuleManager                                               $ruleManager
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory
     */
    public function __construct(
        \Aitoc\Gifts\Model\Manager\ItemManager $itemManager,
        \Magento\Store\Model\Store $store,
        \Aitoc\Gifts\Model\Manager\RuleManager $ruleManager,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory
    ) {
        $this->itemManager = $itemManager;
        $this->store = $store;
        $this->ruleManager = $ruleManager;
        $this->discountDataFactory = $discountDataFactory;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return Data
     */
    public function calculatePercentDiscount($item, $rule)
    {
        $percent = $this->validatePercentValue($this->ruleManager->getDiscountPercent($rule));
        $itemPrice = $this->itemManager->getItemPrice($item);
        $calculatedPrice = $itemPrice * $percent / 100;
        $discountAmount = $calculatedPrice * $item->getQty();

        $discountAmount = [
            'discountAmount' => $discountAmount,
            'itemPrice' => $calculatedPrice,
        ];

        return $this->getDiscountData($discountAmount);
    }

    /**
     * @param $value
     *
     * @return float
     */
    public function validatePercentValue($value)
    {
        $chars = ['%', '-'];

        foreach ($chars as $char) {
            if (strpos($value, $char) !== false) {
                $value = (float)str_replace($char, "", $value);
            }
        }

        return $value;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return Data
     */
    public function calculateAmountDiscount($item, $rule)
    {
        $amount = $this->ruleManager->getDiscountAmount($rule);
        $itemPrice = $this->itemManager->getItemPrice($item);
        $fullPrice = $itemPrice * $item->getQty();

        if (($itemPrice - $amount) < 0) {
            $discountAmount = [
                'discountAmount' => $itemPrice * $item->getQty(),
                'itemPrice' => 0,
            ];
        } else {
            $calculatedPrice = $itemPrice - $amount;
            $discountAmount = [
                'discountAmount' => $fullPrice - ($calculatedPrice * $item->getQty()),
                'itemPrice' => $calculatedPrice,
            ];
        }

        return $this->getDiscountData($discountAmount);
    }

    /**
     * @param $discountAmount
     *
     * @return float|int
     */
    private function getDiscountAmount($discountAmount)
    {
        return $discountAmount * $this->store->getCurrentCurrencyRate();
    }

    /**
     * @param $discountAmount
     *
     * @return array
     */
    private function getDiscountData($discountAmount)
    {
        /** @var Data $discountData */
        $discountData = $this->createDiscountData();
        $calculatedPrice = $this->getDiscountAmount($discountAmount['itemPrice']);
        $discountAmount = $this->getDiscountAmount($discountAmount['discountAmount']);
        $discountData->setAmount($discountAmount)
            ->setBaseAmount($discountAmount)
            ->setOriginalAmount($discountAmount)
            ->setBaseOriginalAmount($discountAmount);

        return ['discountData' => $discountData, 'calculatedPrice' => $calculatedPrice];
    }

    /**
     * @return Data
     */
    private function createDiscountData()
    {
        return $this->discountDataFactory->create();
    }
}
