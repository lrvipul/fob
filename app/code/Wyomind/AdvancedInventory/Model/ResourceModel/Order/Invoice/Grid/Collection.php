<?php

namespace Wyomind\AdvancedInventory\Model\ResourceModel\Order\Invoice\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * Collection constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(EntityFactory $entityFactory, Logger $logger, FetchStrategy $fetchStrategy, EventManager $eventManager, $mainTable = 'sales_invoice_grid', $resourceModel = \Magento\Sales\Model\ResourceModel\Order\Invoice::class)
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    protected function _initSelect()
    {
        $this->addFilterToMap('store_id', 'main_table.store_id');
        parent::_initSelect();
    }
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_array($field)) {
            foreach ($field as $key => $value) {
                if ($key == "entity_id") {
                    unset($field[$key]);
                    $key = "main_table." . $key;
                    $field[$key] = $value;
                }
            }
        } else {
            if ($field == "entity_id") {
                $field = "main_table." . $field;
            }
        }
        return parent::addFieldToFilter($field, $condition);
    }
    protected function _beforeLoad()
    {
        $sog = $this->getTable("sales_order_grid");
        $this->getSelect()->joinLeft($sog, $sog . ".entity_id = main_table.order_id", ["assigned_to" => "assigned_to", "order_status" => $sog . ".status"]);
        parent::_beforeLoad();
    }
}