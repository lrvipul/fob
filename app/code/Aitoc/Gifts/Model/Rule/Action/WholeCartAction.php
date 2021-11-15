<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Rule\Action;

class WholeCartAction extends ActionAbstract
{
    /**
     * {@inheritdoc}
     */
    public function actionProcess($quote, $rule)
    {
        $items = $this->getItems($rule);
        $this->addItemsInCart($rule, $items, $quote);
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     * @param null                     $quote
     *
     * @return array
     */
    public function getItems($rule, $quote = null)
    {
        return $this->getItemsFromRule($rule);
    }
}
