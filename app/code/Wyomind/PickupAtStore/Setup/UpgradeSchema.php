<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PickupAtStore\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \Wyomind\Framework\Helper\ModuleFactory
     */
    public $framework;

    /**
     * UpgradeSchema constructor.
     * @param \Wyomind\Framework\Helper\ModuleFactory $license
     */
    public function __construct(\Wyomind\Framework\Helper\License\UpdateFactory $license)
    {
        $this->framework = $license;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup,
                            ModuleContextInterface $context)
    {
        $this->framework->create()->update(__CLASS__, $context);
        $setup->startSetup();

        if (version_compare($context->getVersion(), "3.1.1", "<")) {
            $orderTable = 'sales_order';
            $setup->getConnection("checkout")->addColumn(
                $setup->getTable($orderTable), 'pickup_store', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 10,
                    'comment' => 'Pickup Store'
                ]
            );
        }

        if (version_compare($context->getVersion(), "3.2.0", "<")) {
            $pointofsale = 'pointofsale';
            $setup->getConnection()->addColumn(
                $setup->getTable($pointofsale), 'pickup_fee', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    'comment' => 'Pickup fee'
                ]
            );
        }

        // Option that defines if the handling fee is global or specific to a store view
        if (version_compare($context->getVersion(), "3.3.0", "<")) {
            $pointofsale = 'pointofsale';
            $setup->getConnection()->addColumn(
                $setup->getTable($pointofsale),
                'pos_handling_fee', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 1,
                    'default' => 0,
                    'comment' => 'Handling fee type (global/specific)'
                ]
            );
        }

        if (version_compare($context->getVersion(), "4.0.0", "<")) {
            $pointofsale = 'pointofsale';
            $setup->getConnection()->addColumn(
                $setup->getTable($pointofsale), 'pos_minimal_delay', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 1,
                    'default' => 0,
                    'comment' => 'use global configuration for the minimal delay for handling the order?'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable($pointofsale), 'minimal_delay', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'minimal delay for handling the order'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable($pointofsale), 'pos_minimal_delay_backorder', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 1,
                    'default' => 0,
                    'comment' => 'use global configuration for the minimal delay for handling the backorder?'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable($pointofsale), 'minimal_delay_backorder', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'minimal delay for handling the backorder'
                ]
            );
        }

        if (version_compare($context->getVersion(), '4.3.0') < 0) {
            $tableName = $setup->getTable('pointofsale');

            if ($setup->getConnection()->isTableExists($tableName) == true) {
                // calendar_color
                $setup->getConnection()->addColumn(
                    $tableName, 'calendar_color', ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 25, 'nullable' => true, "comment" => 'Color to use in the calendar for the source']
                );
                // nb_slots
                $setup->getConnection()->addColumn(
                    $tableName, 'nb_slots', ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 11, 'nullable' => false, "default" => 0, "comment" => 'Number of day/time slots allowed for the source']
                );
            }
        }

        $setup->endSetup();
    }
}