<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeData
 * @package Wyomind\PointOfSale\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * UpgradeData constructor.
     * @param \Magento\Framework\App\State $state
     * @param \Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\CollectionFactory $collectionFactory
    )
    {
        $this->state = $state;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        if (version_compare($context->getVersion(), '7.0.4') < 0) {
            try {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            } catch (\Exception $e) {

            }

            $installer = $setup;
            $installer->startSetup();

            $collection = $this->collectionFactory->create();
            foreach ($collection as $pos) {
                if ($pos->getStatus() == 1) {
                    $pos->setData('visible_store_locator',1);
                    $pos->setData('visible_product_page',1);
                    $pos->setData('visible_checkout',1);
                    $pos->save();
                }
            }

            $installer->endSetup();
        }
    }
}
