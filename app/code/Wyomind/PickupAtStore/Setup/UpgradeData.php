<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PickupAtStore\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\Collection;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;
    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;
    /**
     * @var Collection
     */
    private $_posCollection;

    /**
     * @var State
     */
    private $_state;

    /** @var EavSetupFactory $eavSetupFactory */
    private $_eavSetupFactory;

    /**
     * @param Collection $posCollection
     * @param State $state
     * @param EavSetupFactory $eavSetupFactory
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        Collection $posCollection,
        State $state,
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    )
    {
        $this->_posCollection = $posCollection;
        $this->_state = $state;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
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
        $setup->startSetup();
        if (version_compare($context->getVersion(), '3.3.0', '<')) {
            try {
                $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            } catch (\Exception $e) {

            }

            foreach ($this->_posCollection as $pos) {
                $fee = $pos->getPickupFee();

                if ($fee) {
                    $pos->setPosHandlingFee(1);
                    $pos->save();
                }
            }
        }


        if (version_compare($context->getVersion(), '3.5.1') < 0) {


            $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'disallow_pickupatstore',
                [
                    'group' => "General",
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Disallow store pickup?',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => false,
                    'default' => '0',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );


        }

        if (version_compare($context->getVersion(), '6.1.0') < 0) {
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();

            /** @var $attributeSet AttributeSet */
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            $customerSetup->addAttribute(Customer::ENTITY, 'preferred_store', [
                'type' => 'int',
                'label' => 'Preferred Store for pickup',
                'input' => 'select',
                'required' => false,
                'visible' => true,
                'source' => 'Wyomind\PickupAtStore\Model\Eav\Source\PointOfSale',
                'backend' => '',
                'user_defined' => false,
                'is_user_defined' => false,
                'sort_order' => 1000,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false,
                'position' => 1000,
                'default' => 0,
                'system' => 0,
            ]);

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'preferred_store')
                ->addData([
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer'],
                ]);

            $attribute->save();


            // updating sales_order_grid.pickup_datetime column
            $sog = $setup->getTable("sales_order_grid");
            $so = $setup->getTable("sales_order");
            $setup->getConnection()->query("UPDATE " . $sog . " sog SET pickup_datetime = (SELECT pickup_datetime from " . $so . " so WHERE so.entity_id = sog.entity_id)");

        }

        $setup->endSetup();
    }
}