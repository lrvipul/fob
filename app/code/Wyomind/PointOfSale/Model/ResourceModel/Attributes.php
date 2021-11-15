<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Model\ResourceModel;

/**
 * Class Attributes
 * @package Wyomind\PointOfSale\Model\ResourceModel
 */
class Attributes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var Attributes\CollectionFactory
     */
    protected $attributesCollectionFactory;
    /**
     * Attributes constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Attributes\CollectionFactory $attributesCollectionFactory
     * @param string|null $connectionName
     */
    public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context, \Wyomind\PointOfSale\Model\ResourceModel\Attributes\CollectionFactory $attributesCollectionFactory, string $connectionName = null)
    {
        $this->attributesCollectionFactory = $attributesCollectionFactory;
        parent::__construct($context, $connectionName);
    }
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('pointofsale_attributes', 'attribute_id');
    }
    /**
     * @param \Magento\Framework\Model\AbstractModel $attribute
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Exception
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $attribute)
    {
        $disallowed = ["link", "code", "name", "phone", "email", "address_1", "address_2", "city", "state", "country", "zipcode", "hours", "description", "image", "google_map"];
        if ($attribute->getCode() == "") {
            $label = $attribute->getLabel();
            $code = preg_replace("/[^ a-z0-9_]+/", "", strtolower($label));
            $code = str_replace(" ", "_", $code);
            if (in_array($code, $disallowed)) {
                throw new \Exception(__('The attribute code <i>%1</i> is reserved.', $code));
            }
            $attribute->setCode($code);
            $collection = $this->attributesCollectionFactory->create()->getByCode($code);
            $collection->addFieldToFilter("attribute_id", ["neq" => $attribute->getId()]);
            if (count($collection) > 0) {
                throw new \Exception(__('An attribute with the same code already exists'));
            }
        }
        return parent::_beforeSave($attribute);
    }
}