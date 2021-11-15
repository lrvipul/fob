<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Ui\DataProvider;

class JournalProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Wyomind\AdvancedInventory\Model\ResourceModel\Journal\CollectionFactory
     */
    protected $collection;
    /**
     * Class constructor
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Wyomind\AdvancedInventory\Model\ResourceModel\Journal\CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct($name, $primaryFieldName, $requestFieldName, \Wyomind\AdvancedInventory\Model\ResourceModel\Journal\CollectionFactory $collectionFactory, array $meta = [], array $data = [])
    {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}