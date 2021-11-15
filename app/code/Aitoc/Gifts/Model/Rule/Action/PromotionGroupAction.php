<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Rule\Action;

class PromotionGroupAction extends ActionAbstract
{
    /**
     * {@inheritdoc}
     */
    public function actionProcess($quote, $rule)
    {
        $items = $this->replaceItemsFromRule($rule,
            $this->itemManager->validateItemByQtyStep(
                $this->ruleManager->getDiscountStep($rule),
                $this->getItems($rule, $quote)
            )
        );
        $this->addItemsInCart($rule, $items, $quote);
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return array
     */
    protected function getItems($rule, $quote = null)
    {
        $resultItems = [];
        if ($items = $this->cartManager->getAllNonGiftItems($quote)) {
            foreach ($items as $item) {
                if (!$this->ruleManager->validateByActionsItem($rule, $item)) {
                    continue;
                }

                $resultItems[] = $item;
            }
        }

        return $resultItems;
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param array $items
     *
     * @return array
     */
    protected function replaceItemsFromRule($rule, $items)
    {
        if ($items) {
            foreach ($items as $key => $item) {
                if (isset($item['product'], $item['count'])) {
                    $ruleItems = $this->getItemsFromRule($rule);

                    if (!$ruleItems) {
                        unset($items[$key]);
                        continue;
                    }

                    $items[$key]['product'] = array_shift($ruleItems);
                }
            }
        }

        return $items;
    }
}
