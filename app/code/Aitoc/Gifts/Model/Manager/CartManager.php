<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Manager;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Aitoc\Gifts\Controller\RegistryConstants;

class CartManager implements ManagerInterface
{
    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageMaanger;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        \Aitoc\Gifts\Model\Manager\ItemManager $itemManager,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Message\ManagerInterface $messageMaanger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->itemManager = $itemManager;
        $this->cart = $cart;
        $this->messageMaanger = $messageMaanger;
        $this->eventManager = $eventManager;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $productSku
     * @param                  $qty
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param                  $requestParams
     * @param Quote|null       $quote
     *
     * @throws \Exception
     */
    public function addProductToCart($productSku, $qty,  $rule, $requestParams = [], Quote $quote = null)
    {
        if (!$qty || !$rule->getId()) {
            throw new \Exception(__('We can\'t add this FREE item to your shopping cart right now.'));
        }
        $product = $this->productRepository->get($productSku, false, null, true);
        $product->setData(RegistryConstants::CURRENT_RULE_ID, $rule->getId());
        $qty = $this->getQtyToAdd($product, $qty, $quote);

        if (!$qty) {
            return $this;
        }

        $requestInfo = $this->getRequestInfo($qty, $requestParams, $rule->getId());
        $cart = $this->addQuoteToCart($quote);
        $cartQuote = $cart->getQuote();
        $item = $cartQuote->addProduct($product, new \Magento\Framework\DataObject($requestInfo));

        if ($item instanceof Item) {
            $this->collectTotals($item, $cartQuote);

            $this->eventManager->dispatch(
                RegistryConstants::AITOC_GIFTS_PRODUCT_ADD_TO_CART_EVENT_NAME,
                [
                    'quote' => $cartQuote,
                    'item' => $item,
                    'rule' => $rule,
                    'requestInfo' => $requestInfo
                ]
            );
        } else {
            throw new LocalizedException(__($item));
        }

        if (!$this->request->isAjax() || $this->request->getActionName() == 'add') {
            $this->messageMaanger->addSuccessMessage(
                __("GIFT Item %1 was added to your shopping cart. Qty: %2", $product->getName(), $qty)
            );
        }
    }

    /**
     * @param Item $item
     * @param Quote $cartQuote
     */
    private function collectTotals(Item $item, Quote $cartQuote)
    {
        if ($item->getProductType() !== Configurable::TYPE_CODE) {
            $items = $cartQuote->getShippingAddress()->getAllItems();
            $items[] = $item;
            $cartQuote->getShippingAddress()->setCollectShippingRates(true);
            $cartQuote->getShippingAddress()->setData('cached_items_all', $items);
            $cartQuote->setTotalsCollectedFlag(false)->collectTotals();
        }
    }

    /**
     * @param $quote
     *
     * @return mixed
     */
    public function collectDeletedItems($quote)
    {
        $items = $quote->getShippingAddress()->getAllItems();
        foreach ($items as $key => $item) {
            if ($item->isDeleted()) {
                unset($items[$key]);
            }
        }

        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->setData('cached_items_all', $items);
        $quote->setTotalsCollectedFlag(false)->collectTotals();

        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->setData('cached_items_all', $items);
        $quote->setTotalsCollectedFlag(false)->collectTotals();

        return $quote;
    }

    /**
     * @param $quote
     *
     * @return \Magento\Checkout\Model\Cart
     */
    private function addQuoteToCart($quote)
    {
        $cart = $this->cart;

        if (!$this->cart->hasData('quote')) {
            $cart->setQuote($quote);
        }

        return $cart;
    }

    /**
     * @param $qty
     * @param $requestParams
     * @param $ruleId
     *
     * @return array
     */
    private function getRequestInfo($qty, $requestParams, $ruleId)
    {
        $requestInfo = [
            'qty' => $qty,
            'options' => []
        ];

        if (!empty($requestParams)) {
            $requestInfo = array_merge_recursive($requestParams, $requestInfo);
        }

        $requestInfo['options'][RegistryConstants::CURRENT_RULE_ID] = $ruleId;

        return $requestInfo;
    }

    /**
     * @param $product
     * @param $requestedQty
     * @param $quote
     *
     * @return bool
     */
    public function getQtyToAdd($product, $requestedQty, $quote)
    {
        return $this->itemManager->checkExistQty($product, $requestedQty, $quote);
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function setAppliedRules($rule, $quote)
    {
        $resultArray = [];
        $ruleId = $rule->getRuleId();

        if ($quote->getData(RegistryConstants::AITOC_GIFTS_APPLIED_RULES_FIELD_NAME)) {
            $appliedRules = explode(',', $quote->getData(RegistryConstants::AITOC_GIFTS_APPLIED_RULES_FIELD_NAME));
            array_push($appliedRules, $ruleId);
            $resultArray = array_unique($appliedRules);
        } else {
            array_push($resultArray, $ruleId);
        }

        $quote->setData(RegistryConstants::AITOC_GIFTS_APPLIED_RULES_FIELD_NAME, implode(',', $resultArray));

        return $quote;
    }

    /**
     * @param int|string $ruleId
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return boolean
     */
    public function ruleIsAlreadyApplied($ruleId, $quote)
    {
        if ($quote->getData(RegistryConstants::AITOC_GIFTS_APPLIED_RULES_FIELD_NAME)) {
            return in_array(
                $ruleId,
                explode(',', $quote->getData(RegistryConstants::AITOC_GIFTS_APPLIED_RULES_FIELD_NAME))
            );
        }

//        if (in_array($ruleId, $this->statisticRepository->getAppliedRulesIds($quote))) {
//            return true;
//        }

        return false;
    }

    /**
     * @param $quote
     * @param bool $asArray
     * @return array
     */
    public function getAllDeletedItems($quote, $asArray = true)
    {
        $resultItems = [];

        if ($quote->getItems()) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getItems() as $item) {
                if ($item->isDeleted()) {
                    if ($asArray) {
                        $resultItems[$item->getSku()] = $item->getQty();
                    } else {
                        $resultItems[$item->getSku()] = $item;
                    }
                }
            }
        }

        return $resultItems;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return array
     */
    public function getAllNonGiftItems($quote)
    {
        $items = [];

        $giftItems = $this->objectManager
            ->create(\Aitoc\Gifts\Model\Repository\StatisticRepository::class)
            ->getGiftItemInQuoteFromStatistic($quote, false);

        if ($quote && $quote->getAllItems()) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getAllItems() as $item) {
                if (!$this->itemManager->getRuleIdFromItem($item)
                    && !$item->isDeleted()
                    && $item->getProductType() !== Configurable::TYPE_CODE
                ) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * @param $sku
     * @param $giftItems
     *
     * @return bool
     */
    public function findGiftInCartBySku($sku, $giftItems)
    {
        $isFind = false;

        foreach ($giftItems as $item) {
            if ($item['sku'] == $sku) {
                $isFind = true;

                break;
            }
        }

        return $isFind;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function flushQuote($quote)
    {
        if ($quote->getAllItems()) {
            foreach ($quote->getAllItems() as $item) {
                $quote->removeItem($item->getItemId());
            }
        }

        return $quote;
    }
}
