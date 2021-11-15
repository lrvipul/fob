<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
/**
 * 6.2 Define form and grid widgets
 */
namespace Wyomind\PointOfSale\Ui\DataProvider;

/**
 * Class AttributesProvider
 * @package Wyomind\PointOfSale\Ui\DataProvider
 */
class AttributesProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var
     */
    protected $collection;
    /**
     * @var array 
     */
    protected $_loadedData;
    /**
     * AttributesProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Wyomind\PointOfSale\Model\ResourceModel\Attributes\CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct($name, $primaryFieldName, $requestFieldName, \Wyomind\PointOfSale\Model\ResourceModel\Attributes\CollectionFactory $collectionFactory, array $meta = [], array $data = [])
    {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}