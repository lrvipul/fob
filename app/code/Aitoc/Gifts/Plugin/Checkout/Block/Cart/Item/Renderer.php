<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Plugin\Checkout\Block\Cart\Item;

use Magento\Checkout\Block\Cart\Item\Renderer as RendererItem;

class Renderer
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Aitoc\Gifts\Model\Manager\ItemManager
     */
    private $itemManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Aitoc\Gifts\Model\Manager\ItemManager $itemManager
    ) {
        $this->scopeConfig     = $scopeConfig;
        $this->itemManager     = $itemManager;
    }

    /**
     * @param Renderer $subject
     * @param string   $result
     *
     * @return string
     */
    public function afterGetProductName(RendererItem $subject, $result)
    {
        if ($item = $subject->getItem()) {
            return $this->itemManager->convertItemNameToGiftName($item, $result);
        }

        return $result;
    }

    /**
     * @param RendererItem $subject
     * @param \Closure $proceed
     * @return mixed|string
     */
    public function aroundGetTemplate(RendererItem $subject, \Closure $proceed)
    {
        if ($subject->getItem() && $this->itemManager->isGiftItem($subject->getItem())) {
            return 'Aitoc_Gifts::cart/item/default.phtml';
        }

        return $proceed($subject);
    }

    /**
     * @param RendererItem $subject
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return array
     */
    public function beforeGetRowTotalHtml(RendererItem $subject, \Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        if ($this->itemManager->isGiftItem($item)) {
            $item->setRowTotal($item->getRowTotal() - $item->getDiscountAmount());
            $item->setBaseRowTotal($item->getBaseRowTotal() - $item->getBaseDiscountAmount());
            $item->setRowTotalInclTax($item->getRowTotalInclTax() - $item->getDiscountAmount());
            $item->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount());
        }

        return [$item];
    }
}
