<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Rule\Action;

use Aitoc\Gifts\Model\Rule\Type\DiscountType;
use Aitoc\Gifts\Model\Rule\Type\PromotionAddType;

abstract class ActionAbstract implements ActionInterface
{
    /**
     * @var \Magento\SalesRule\Model\Validator
     */
    protected $validator;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Aitoc\Gifts\Model\Manager\ItemManager
     */
    protected $itemManager;

    /**
     * @var \Aitoc\Gifts\Model\Manager\RuleManager
     */
    protected $ruleManager;

    /**
     * @var \Aitoc\Gifts\Model\Manager\CartManager
     */
    protected $cartManager;

    /**
     * @var \Aitoc\Gifts\Model\Manager\DiscountManager
     */
    private $discountManager;

    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Aitoc\Gifts\Model\Manager\ItemManager $itemManager,
        \Aitoc\Gifts\Model\Manager\RuleManager $ruleManager,
        \Aitoc\Gifts\Model\Manager\CartManager $cartManager,
        \Aitoc\Gifts\Model\Manager\DiscountManager $discountManager
    ) {
        $this->validator       = $validator;
        $this->priceCurrency   = $priceCurrency;
        $this->productRepository = $productRepository;
        $this->itemManager = $itemManager;
        $this->ruleManager = $ruleManager;
        $this->cartManager = $cartManager;
        $this->discountManager = $discountManager;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float                                        $qty
     *
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculateDiscount($rule, $item, $qty)
    {
        $discountData = [];

        switch ($this->ruleManager->getDiscountType($rule)) {
            case DiscountType::DISCOUNT_AMOUNT_TYPE:
                $discount = $this->discountManager->calculateAmountDiscount($item, $rule);
                $discountData = [
                    'type' => DiscountType::DISCOUNT_AMOUNT_TYPE,
                    'discountData' => $discount['discountData'],
                    'itemPrice' => $discount['calculatedPrice'],
                ];
                break;
            case DiscountType::DISCOUNT_PERCENT_TYPE:
                $discount = $this->discountManager->calculatePercentDiscount($item, $rule);
                $discountData = [
                    'type' => DiscountType::DISCOUNT_PERCENT_TYPE,
                    'discountData' => $discount['discountData'],
                    'itemPrice' => $discount['calculatedPrice'],
                ];
                break;
        }

        return $discountData;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Aitoc\Gifts\Model\Rule
     *
     * @return \Magento\Quote\Model\Quote
     */
    abstract public function actionProcess($quote, $rule);

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return array
     */
    protected function getItems($rule, $quote = null)
    {
        $items = [];

        return $items;
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return array
     */
    protected function getItemsFromRule($rule, $quote = null)
    {
        $shuffle_assoc = function (&$array) {
            $keys = array_keys($array);
            shuffle($keys);

            foreach($keys as $key) {
                $new[$key] = $array[$key];
            }

            $array = $new;

            return true;
        };

        $items = $this->itemManager->getPromotionalItems($rule);

        if ($this->ruleManager->getRulePromotionalItemsAddAlgorithm($rule)
            == PromotionAddType::ONE_OF_ITEM_OPTION_VALUE
        ) {
            $shuffle_assoc($items);

            return array_slice($items, 0, 1, true);
        }

        return $items;
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item $items
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return boolean
     */
    protected function addItemsInCart($rule, $items, $quote)
    {
        foreach ($items as $key => $item) {
            $this->cartManager->addProductToCart(
                isset($item['product']) ? $item['product']->getData()['sku'] : $item->getSku(),
                $this->ruleManager->getQtyToAdd($rule, isset($item['count']) ? $item['count'] : null),
                $rule,
                [],
                $quote
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function calculateAndApplyDiscount($rule, $quote)
    {
        foreach ($quote->getAllItems() as $item) {
            if ($this->itemManager->isGiftItem($item, $rule->getId()) == $rule->getRuleId()) {
                $this->applyDiscount($rule, $item);
            }
        }

        return $this;
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return  $this
     */
    private function applyDiscount($rule, $item)
    {
        $discountData = $this->calculateDiscount($rule, $item, $item->getQty());

        switch ($discountData['type']) {
            case DiscountType::DISCOUNT_AMOUNT_TYPE:
                $this->itemManager->setDiscountData($discountData['discountData'], $item, $discountData['itemPrice']);
                break;
            case DiscountType::DISCOUNT_PERCENT_TYPE:
                /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
                $this->itemManager->setDiscountData($discountData['discountData'], $item, $discountData['itemPrice']);
                break;
        }

        return $this;
    }
}
