<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Rule\Action;

class AmountCartAction extends ActionAbstract
{
    /**
     * {@inheritdoc}
     */
    public function actionProcess($quote, $rule)
    {
        $innerStep = $this->itemManager->getItemsQtyByCartAmount($this->ruleManager->getCartAmount($rule), $quote);

        if (($items = $this->getItemsFromRule($rule)) && $innerStep) {
            $items = $this->prepareItemsFromRule($this->getItemsFromRule($rule), $innerStep, $rule);
            $this->addItemsInCart($rule, $items, $quote);
        }
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
                isset($item['count']) ? $item['count'] :
                    $this->ruleManager->getQtyToAdd($rule, 1),
                $rule,
                [],
                $quote
            );
        }

        return true;
    }

    /**
     * @param array $items
     * @param array $innerStep
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return array
     */
    protected function prepareItemsFromRule($items, $innerStep, $rule)
    {
        $resultItems = [];
        foreach ($items as $item) {
            $resultItems[] = [
                'product' => $item,
                'count' => $this->ruleManager->getQtyToAdd($rule, $innerStep),
            ];
        }

        return $resultItems;
    }
}
