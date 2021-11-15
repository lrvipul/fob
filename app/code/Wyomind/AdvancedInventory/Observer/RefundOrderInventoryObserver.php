<?php

namespace Wyomind\AdvancedInventory\Observer;

class RefundOrderInventoryObserver extends \Magento\SalesInventory\Observer\RefundOrderInventoryObserver
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;
    /**
     * @var StockManagementInterface
     */
    private $stockManagement;
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    private $stockIndexerProcessor;
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    private $priceIndexer;
    /**
     * @var \Magento\SalesInventory\Model\Order\ReturnProcessor
     */
    private $returnProcessor;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    public function __construct(\Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration, \Magento\CatalogInventory\Api\StockManagementInterface $stockManagement, \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor, \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer, \Magento\SalesInventory\Model\Order\ReturnProcessor $returnProcessor, \Magento\Sales\Api\OrderRepositoryInterface $orderRepository)
    {
        parent::__construct($stockConfiguration, $stockManagement, $stockIndexerProcessor, $priceIndexer, $returnProcessor, $orderRepository);
        $this->stockConfiguration = $stockConfiguration;
        $this->stockManagement = $stockManagement;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->priceIndexer = $priceIndexer;
        $this->returnProcessor = $returnProcessor;
        $this->orderRepository = $orderRepository;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $itemsToUpdate = [];
        foreach ($creditmemo->getAllItems() as $item) {
            $qty = $item->getQty();
            // Wyomind - Advanced Inventory
            // if (($item->getBackToStock() && $qty) || $this->stockConfiguration->isAutoReturnEnabled()) {
            if ($item->getBackToStock() && $qty && $this->stockConfiguration->isAutoReturnEnabled()) {
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
            $this->stockManagement->revertProductsSale($itemsToUpdate, $creditmemo->getStore()->getWebsiteId());
            $updatedItemIds = array_keys($itemsToUpdate);
            $this->stockIndexerProcessor->reindexList($updatedItemIds);
            $this->priceIndexer->reindexList($updatedItemIds);
        }
    }
}