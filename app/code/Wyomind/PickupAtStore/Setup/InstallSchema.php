<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PickupAtStore\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install(
    SchemaSetupInterface $setup,
            ModuleContextInterface $context
    )
    {


        $setup->startSetup();

        $quote = 'quote';
        $orderTable = 'sales_order';
        $orderTableGrid = 'sales_order_grid';

        // quote
        $setup->getConnection("checkout")
                ->addColumn(
                        $setup->getTable($quote), 'pickup_datetime', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Pickup Datetime'
                        ]
        );

        $setup->getConnection("checkout")->addColumn(
                $setup->getTable($quote), 'pickup_store', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 10,
            'comment' => 'Pickup Store'
                ]
        );
        // sales_order
        $setup->getConnection("sales")
                ->addColumn(
                        $setup->getTable($orderTable), 'pickup_datetime', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Pickup Datetime'
                        ]
        );

        $setup->getConnection("sales")->modifyColumn(
                $setup->getTable($orderTable), "shipping_description", [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 1000,
            'comment' => 'Shipping Description'
                ]
        );
        
        
        $setup->getConnection()
                ->addColumn(
                        $setup->getTable($orderTableGrid), 'pickup_datetime', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Pickup Datetime'
                        ]
        );


        $setup->endSetup();
    }

}
