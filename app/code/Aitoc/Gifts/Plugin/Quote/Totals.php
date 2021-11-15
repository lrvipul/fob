<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Plugin\Quote;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Aitoc\Gifts\Controller\RegistryConstants;
use Aitoc\Gifts\Model\Rule\Type\DiscountType;

class Totals
{
    /**
     * @var \Aitoc\Gifts\Model\Manager\ItemManager
     */
    private $itemManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    public function __construct(
        \Aitoc\Gifts\Model\Manager\ItemManager $itemManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->itemManager = $itemManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Quote\Model\Cart\Totals $subject
     * @param                                  $items
     *
     * @return array
     */
    public function beforeSetItems(\Magento\Quote\Model\Cart\Totals $subject, $items)
    {
        $quote = $this->checkoutSession->getQuote();

        if ($items && $quote->getId()) {
            $items = $this->updateTotalItems($items, $quote);
        }

        return [$items];
    }

    /**
     * @param $items
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return mixed
     */
    private function updateTotalItems($items, $quote)
    {
        if ($items) {
            /** @var \Magento\Quote\Api\Data\TotalsItemInterface $item */
            foreach ($items as $key => $item) {
                if (is_array($item) && isset($item['item_id'])) {
                    $quoteItem = $quote->getItemById($item['item_id']);
                    if ($this->itemManager->isGiftItem($quoteItem)) {
                        $item['name'] = $this->itemManager->convertItemNameToGiftName($quoteItem, $item['name']);
                        $item['row_total'] = $item['row_total'] - $item['discount_amount'];
                        $item['base_row_total'] = $item['base_row_total'] - $item['base_discount_amount'];
                        $item['row_total_incl_tax'] = $item['row_total_incl_tax'] - $item['discount_amount'];
                        $item['base_row_total_incl_tax'] = $item['base_row_total_incl_tax']
                            - $item['base_discount_amount'];
                     }

                    $items[$key] = $item;
                }
            }
        }

        return $items;
    }
}
