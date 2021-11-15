<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Model\ResourceModel;

/**
 * Point of sale mysql resource
 */
class PointOfSale extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var AttributesValues\CollectionFactory
     */
    protected $attributesValuesCollectionFactory;
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Framework\Model\ResourceModel\Db\Context $context, AttributesValues\CollectionFactory $attributesValuesCollectionFactory, string $connectionName = null)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->attributesValuesCollectionFactory = $attributesValuesCollectionFactory;
        parent::__construct($context, $connectionName);
    }
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('pointofsale', 'place_id');
    }
    /**
     * @param \Magento\Framework\Model\AbstractModel $pointOfSale
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $pointOfSale)
    {
        $attributesValues = $this->attributesValuesCollectionFactory->create()->getByPointOfSaleId($pointOfSale->getId());
        foreach ($attributesValues as $attributeValue) {
            $pointOfSale->setData($attributeValue->getCode(), $attributeValue->getValue());
        }
        return parent::_afterLoad($pointOfSale);
    }
    /**
     * @param \Magento\Framework\Model\AbstractModel $pointOfSale
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $pointOfSale)
    {
        if ($pointOfSale->getData('use_assignation_rules') !== 0) {
            $toInsertFields = ["attribute_id", "pointofsale_id", "value"];
            $toInsertData = [];
            foreach ($this->attributesCollection as $attribute) {
                $code = $attribute->getCode();
                $value = $pointOfSale->getData($code);
                $attributeId = $attribute->getId();
                $toInsertData[] = ["attribute_id" => $attributeId, "pointofsale_id" => $pointOfSale->getId(), "value" => $value];
            }
            $this->_resources->getConnection()->insertOnDuplicate($this->_resources->getTableName("pointofsale_attributes_values"), $toInsertData, $toInsertFields);
        }
        return parent::_afterSave($pointOfSale);
    }
}