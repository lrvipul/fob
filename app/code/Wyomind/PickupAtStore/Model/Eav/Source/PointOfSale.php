<?php

namespace Wyomind\PickupAtStore\Model\Eav\Source;

use Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\Collection;
use Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\CollectionFactory;
class PointOfSale extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * PointOfSale constructor.
     * @param CollectionFactory $collection
     */
    public function __construct(CollectionFactory $collection)
    {
        $this->collectionFactory = $collection;
    }
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [['label' => 'N/A', 'value' => '']];
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            foreach ($collection->getData() as $pos) {
                $this->_options[] = ['label' => $pos['name'], 'value' => $pos['place_id']];
            }
        }
        return $this->_options;
    }
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
    /**
     * Get a text for option value
     *
     * @param string|int $value
     * @return string|false
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}