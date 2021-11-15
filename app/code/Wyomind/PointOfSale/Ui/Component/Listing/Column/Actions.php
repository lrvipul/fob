<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Ui\Component\Listing\Column;

/**
 * Class Actions
 * @package Wyomind\PointOfSale\Ui\Component\Listing\Column
 */
class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     *
     */
    const editUrl = "pointofsale/attributes/edit";
    /**
     *
     */
    const deleteUrl = "pointofsale/attributes/delete";
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Framework\View\Element\UiComponent\ContextInterface $context, \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory, array $components = [], array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['attribute_id'])) {
                    $item[$name]['edit'] = ['href' => $this->_urlBuilder->getUrl(self::editUrl, ['attribute_id' => $item['attribute_id']]), 'label' => __('Edit')];
                    $item[$name]['delete'] = ['href' => $this->_urlBuilder->getUrl(self::deleteUrl, ['attribute_id' => $item['attribute_id']]), 'label' => __('Delete'), 'confirm' => ['title' => __('Delete an attribute'), 'message' => __('Are you sure you want to delete the attribute <b>%1</b> [<i>%2</i>]?', $item['label'], $item['code'])]];
                }
            }
        }
        return $dataSource;
    }
}