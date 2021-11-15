<?php

namespace Wyomind\AdvancedInventory\Observer;

class RefundOrderInventoryObserver extends \Magento\CatalogInventory\Observer\RefundOrderInventoryObserver
{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $itemsToUpdate = [];
        foreach ($creditmemo->getAllItems() as $item) {
            $qty = $item->getQty();
            // Wyomind - Advanced Inventory
            // if (($item->getBackToStock() && $qty) || $this->stockConfiguration->isAutoReturnEnabled()) {
            if (($item->getBackToStock() && $qty) && $this->stockConfiguration->isAutoReturnEnabled()) {
                $productId = $item->getProductId();
                $parentItemId = $item->getOrderItem()->getParentItemId();
                /* @var $parentItem \Magento\Sales\Model\Order\Creditmemo\Item */
                $parentItem = $parentItemId ? $creditmemo->getItemByOrderId($parentItemId) : false;
                $qty = $parentItem ? $parentItem->getQty() * $qty : $qty;
                if (isset($itemsToUpdate[$productId])) {
                    $itemsToUpdate[$productId] += $qty;
                } else {
                    $itemsToUpdate[$productId] = $qty;
                }
            }
        }
        if (!empty($itemsToUpdate)) {
            $this->stockManagement->revertProductsSale(
                $itemsToUpdate, $creditmemo->getStore()->getWebsiteId()
            );

            $updatedItemIds = array_keys($itemsToUpdate);
            $this->stockIndexerProcessor->reindexList($updatedItemIds);
            $this->priceIndexer->reindexList($updatedItemIds);
        }
    }

}
