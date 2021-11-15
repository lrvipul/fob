<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Model\Attributes;

/**
 * Class DataProvider
 * @package Wyomind\PointOfSale\Model\Attributes
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Wyomind\PointOfSale\Model\ResourceModel\Attributes\Collection
     */
    protected $collection;
    /**
     * @var array
     */
    protected $_loadedData;
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, $name, $primaryFieldName, $requestFieldName, \Wyomind\PointOfSale\Model\ResourceModel\Attributes\CollectionFactory $collectionFactory, array $meta = [], array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $entity) {
            $this->_loadedData[$entity->getAttributeId()] = $entity->getData();
        }
        $data = $this->_dataPersistor->get('attribute');
        if (!empty($data)) {
            $entity = $this->collection->getNewEmptyItem();
            $entity->setData($data);
            $this->_loadedData[$entity->getAttributeId()] = $entity->getData();
            $this->_dataPersistor->clear('attribute');
        }
        return $this->_loadedData;
    }
}