<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package Wyomind\PointOfSale\Setup
 */
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
        $installer = $setup;
        $installer->startSetup();

        // Days off
        if (version_compare($context->getVersion(), '7.0.0', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('pointofsale'),
                'days_off', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Days off'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('pointofsale'),
                'store_page_url_key', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Url Key'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('pointofsale'),
                'store_page_content', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Content of the store page'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('pointofsale'),
                'store_page_enabled', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'size' => 1,
                    'default' => 1,
                    'comment' => 'Is the store page enabled?'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('pointofsale'),
                'store_locator_description_use_global', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'size' => 1,
                    'default' => 1,
                    'comment' => 'Use the global description template?'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('pointofsale'),
                'store_locator_description', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Description of the store when not using the global description'
                ]
            );

            $installer->getConnection()->dropTable($installer->getTable('pointofsale_attributes')); // drop if exists

            $pointofsaleAttributes = $installer->getConnection()
                ->newTable($installer->getTable('pointofsale_attributes'))
                ->addColumn(
                    'attribute_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Attribute ID'
                )
                ->addColumn(
                    'code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['identity' => false, 'unsigned' => true, 'nullable' => false, 'default' => 0, 'primary' => false],
                    'Attribute code'
                )
                ->addColumn(
                    'label',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['identity' => false, 'unsigned' => true, 'nullable' => false, 'default' => 0, 'primary' => false],
                    'Attribute label'
                )->addColumn(
                    'type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    2,
                    ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => 0],
                    'Attribute Type'
                )/*->addColumn(
                    'store_locator',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => 1],
                    'Is attribute available in the store locator'
                )->addColumn(
                    'store_page',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => 1],
                    'Is attribute available in the store page'
                )*/
                ->addIndex(
                    $installer->getIdxName('pointofsale_attributes', ['attribute_id']),
                    ['attribute_id']
                )
                ->setComment('Point of sales and Warehouses Additional Attributes');

            $attributesValues = $setup->getConnection()->newTable($setup->getTable('pointofsale_attributes_values'));

            $attributesValues->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => False, 'identity' => true, 'auto_increment' => true],
                'Value id'
            )->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'attribute_id'
            )->addColumn(
                'pointofsale_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Point of sale id'
            )->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'value'
            )->addIndex(
                $installer->getIdxName('pointofsale_attributes_values', ['value_id']),
                ['value_id']
            )->addIndex(
                $installer->getIdxName('pointofsale_attributes_values', ['pointofsale_id', 'value_id'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE),
                ['pointofsale_id', 'value_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment("Points of sale attributes values");


            $installer->getConnection()->createTable($pointofsaleAttributes);
            $setup->getConnection()->createTable($attributesValues);

        }

        if (version_compare($context->getVersion(), '7.0.4', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('pointofsale'),
                'visible_store_locator', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Visible on store locator'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('pointofsale'),
                'visible_product_page', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Visible on product page'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('pointofsale'),
                'visible_checkout', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' => 'Visible on checkout'
                ]
            );
        }

        $installer->endSetup();
    }

}
