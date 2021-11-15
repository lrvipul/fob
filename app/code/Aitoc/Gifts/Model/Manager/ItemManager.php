<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Manager;

use Magento\Catalog\Model\Product\Type;
use Aitoc\Gifts\Controller\RegistryConstants;
use Aitoc\Gifts\Model\Config;

class ItemManager implements ManagerInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array
     */
    private $allowedProductTypes = ['simple', 'virtual', 'downloadable'];

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface
     */
    private $stockStateProvider;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockRegistryProvider,
        \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider,
        Config $config
    ) {
        $this->productRepository = $productRepository;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockStateProvider = $stockStateProvider;
        $this->config = $config;
    }

    /**
     * @param $rule
     *
     * @return array
     */
    public function getPromotionalItems($rule)
    {
        $items = [];
        $productSkus = explode(',', $rule->getProductSkus());

        if ($productSkus) {
            foreach ($productSkus as $sku) {
                $product = $this->getProductBySku($sku);

                if ($product) {
                    $items[$product->getId()] = $product;
                }
            }
        }

        return $items;
    }

    /**
     * @param $sku
     *
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProductBySku($sku)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product  = $this->productRepository->get($sku);

        if (!$this->validateProduct($product)) {
            return false;
        }

        foreach ($product->getProductOptionsCollection() as $option) {
            $option->setProduct($product);
            $product->addOption($option);
        }

        return $product;
    }

    /**
     * @param $product
     *
     * @return bool
     */
    public function validateProduct($product)
    {
        if (!in_array($product->getTypeId(), $this->allowedProductTypes)) {
            return false;
        }

        if ($product->getTypeId() == Type::TYPE_SIMPLE
            && (!$product->isInStock() || !$product->isSalable()
                || !$this->checkExistQty($product, 1))
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param                                $qtyRequested
     * @param null                           $quote
     *
     * @return float
     */
    public function checkExistQty(\Magento\Catalog\Model\Product $product, $qtyRequested, $quote = null) {
        $stockItem = $this->stockRegistryProvider->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );

        if (!$this->stockStateProvider->checkQty($stockItem, $qtyRequested)) {
            return $stockItem->getQty();
        }

        return $qtyRequested;
    }
    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return array
     */
    public function getRuleItemsFromQuote($rule, $quote)
    {
        $itemsData = [];
        $currentRuleId = $rule->getId();

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllItems() as $key => $item) {
            if (!$this->isGiftItem($item, $currentRuleId) || $item->getParentItemId()) {
                continue;
            }

            $itemsData[] = [
                'sku' => $item->getSku(),
                'qty' => $item->getQty(),
                'price' => $item->getBaseCost()
            ];
        }

        return $itemsData;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param int $ruleId
     *
     * @return boolean
     */
    public function isGiftItem($item, $ruleId = null)
    {
        $buyRequest = $item->getBuyRequest();

        return isset($buyRequest['options'][RegistryConstants::CURRENT_RULE_ID])
            ? $buyRequest['options'][RegistryConstants::CURRENT_RULE_ID] : null;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return mixed|null
     */
    public function getRuleIdFromItem(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        if (!($ruleId = $item->getData(RegistryConstants::CURRENT_RULE_ID))) {
            $buyRequest = $item->getBuyRequest();

            $ruleId = isset($buyRequest['options'][RegistryConstants::CURRENT_RULE_ID])
                ? $buyRequest['options'][RegistryConstants::CURRENT_RULE_ID] : null;

            if ($ruleId) {
                $item->setData(RegistryConstants::CURRENT_RULE_ID, $ruleId);
            }
        }

        return $ruleId;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $itemNewPrice
     *
     * @return $this
     */
    public function setDiscountData($discountData, $item, $itemNewPrice)
    {
        $item->setDiscountAmount($discountData->getAmount());
        $item->setBaseDiscountAmount($discountData->getBaseAmount());
        $item->setOriginalDiscountAmount($discountData->getOriginalAmount());
        $item->setBaseOriginalDiscountAmount($discountData->getBaseOriginalAmount());

        $item->setData(RegistryConstants::AITOC_GIFTS_ITEM_GIFT_PRICE, $itemNewPrice);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $itemNewPrice
     * @param float $discountAmount
     *
     * @return $this
     */
    public function setItemPrice($item, $itemNewPrice, $discountAmount)
    {
        $item->setPrice($itemNewPrice);
        $item->setBaseOriginalPrice($itemNewPrice);
        $item->setConvertedPrice($itemNewPrice);
        $item->setRowTotal($item->getRowTotal() - $discountAmount);
        $item->setBaseRowTotal($item->getBaseRowTotal() - $discountAmount);
        $item->setBasePrice($itemNewPrice);
        $item->setBasePriceInclTax($itemNewPrice);
        $item->setPriceInclTax($itemNewPrice);
        $item->setRowTotalInclTax($item->getRowTotalInclTax() - $itemNewPrice);
        $item->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax() - $itemNewPrice);
        $item->setDiscountCalculationPrice($itemNewPrice);
        $item->setBaseDiscountCalculationPrice($itemNewPrice);
    }

    /**
     * @param $item
     *
     * @return float
     */
    public function getItemPrice($item)
    {
        return $item->getPrice();
    }

    /**
     * @param int $ruleStep
     * @param $items
     *
     * @return array
     */
    public function validateItemByQtyStep($ruleStep, $items)
    {
        $resultItems = [];
        if ($items) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($items as $item) {
                $parent = $item->getParentItem();
                $itemQty = $parent ? $parent->getQty() : $item->getQty();

                if ($innerItemCount = $this->getCountInnerItemQtyInStep($ruleStep, $itemQty)) {
                    $product = null;
                    if ($item->getProductType() == 'bundle') {
                        $product = $item->getProduct();
                    }

                    $resultItems[] = [
                        'product' => $product ?: $this->getProductBySku($item->getSku()),
                        'count' => $innerItemCount
                    ];
                }
            }
        }

        return $resultItems;
    }

    /**
     * @param float $cartAmount
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return float
     */
    public function getItemsQtyByCartAmount($cartAmount, $quote)
    {
        $resultQty = 0;
        if ($cartAmount) {
            $cartSpent = $quote->getBaseGrandTotal();
            $resultQty = $this->getCartSpentStep($cartSpent, $cartAmount);
        }

        return $resultQty;
    }

    /**
     * @param $ruleStep
     * @param $itemQty
     *
     * @return int
     */
    private function getCountInnerItemQtyInStep($ruleStep, $itemQty)
    {
        return $ruleStep && $itemQty ? (int)floor($itemQty / $ruleStep) : 0;
    }

    /**
     * @param $cartSpent
     * @param $cartAmount
     *
     * @return int
     */
    private function getCartSpentStep($cartSpent, $cartAmount)
    {
        return $cartSpent ? (int)floor($cartSpent / $cartAmount) : 0;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param string $itemName
     *
     * @return string
     */
    public function convertItemNameToGiftName($item, $itemName)
    {
        if ($this->isGiftItem($item)) {
            $giftItemNamePostfix = $this->config->getConfigValueByPath(Config::CONFIG_PATH_ITEMS_GIFT_NAME_POSTFIX);
            $itemName = str_replace('$product-name', $itemName, $giftItemNamePostfix);
        }

        return $itemName;
    }
}
