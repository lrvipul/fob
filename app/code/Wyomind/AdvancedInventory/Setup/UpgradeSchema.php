<?php

namespace Wyomind\AdvancedInventory\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \Wyomind\Framework\Helper\ModuleFactory
     */
    public $license;

    /**
     * UpgradeSchema constructor.
     * @param \Wyomind\Framework\Helper\ModuleFactory $license
     */
    public function __construct(\Wyomind\Framework\Helper\License\UpdateFactory $license)
    {
        $this->license = $license;
    }


    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $this->license->create()->update(__CLASS__, $context);

        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '6.2.0') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('pointofsale'), 'stock_status_message',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Stock status message for Advanced Inventory'
                ]
            );
        }

        if (version_compare($context->getVersion(), '6.3.0') < 0) {
            $quoteItem = 'quote_item';
            $salesOrderItem = 'sales_order_item';
            // quote
            $setup->getConnection("checkout")
                ->addColumn(
                    $setup->getTable($quoteItem), 'pre_assignation', [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 5,
                        'comment' => 'Item pre assignation'
                    ]
                );
            // quote
            $setup->getConnection("sales")
                ->addColumn(
                    $setup->getTable($salesOrderItem), 'pre_assignation', [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 5,
                        'comment' => 'Item pre assignation'
                    ]
                );
        }

        if (version_compare($context->getVersion(), "6.7.5", "<")) {
            $pointofsale = 'pointofsale';
            $setup->getConnection()->addColumn(
                $setup->getTable($pointofsale), 'warehouses', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Associated warehouses'
                ]
            );
        }

        if (version_compare($context->getVersion(), "7.0.0", "<")) {
            $pointofsale = 'pointofsale';
            $setup->getConnection()->addColumn(
                $setup->getTable($pointofsale), 'stock_status_message_backorder', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Message to display when the product is backorderable in this pos'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable($pointofsale), 'stock_status_message_out_of_stock', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Message to display when the product is out of stock in this pos'
                ]
            );
        }

        $installer->endSetup();
    }

}
