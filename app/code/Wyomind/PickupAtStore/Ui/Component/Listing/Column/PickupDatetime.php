<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PickupAtStore\Ui\Component\Listing\Column;

use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Locale\ResolverInterface;
/**
 * Render column block in the order grid
 */
class PickupDatetime extends \Magento\Ui\Component\Listing\Columns\Date
{
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, \Magento\Framework\View\Element\UiComponent\ContextInterface $context, \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory, TimezoneInterface $timezone, BooleanUtils $booleanUtils, array $components = [], array $data = [], ResolverInterface $localeResolver = null, DataBundle $dataBundle = null)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        if (version_compare($this->framework->getMagentoVersion(), "2.3.2") > 0) {
            parent::__construct($context, $uiComponentFactory, $timezone, $booleanUtils, $components, $data, $localeResolver, $dataBundle);
        } else {
            parent::__construct($context, $uiComponentFactory, $timezone, $booleanUtils, $components, $data);
        }
    }
    /**
     * Renderer for the pickup datetime chosen by the customer
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        try {
            if (isset($dataSource['data']['items'])) {
                // load orders
                $orderIds = [];
                foreach ($dataSource['data']['items'] as $item) {
                    $orderIds[] = $item['entity_id'];
                }
                $filterGroup = $this->_objectManager->create('\\Magento\\Framework\\Api\\Search\\FilterGroup');
                $filterStoreId = $this->_objectManager->create('\\Magento\\Framework\\Api\\Filter');
                $filterStoreId->setField('entity_id');
                $filterStoreId->setConditionType('in');
                $filterStoreId->setValue($orderIds);
                $filterGroup->setFilters([$filterStoreId]);
                $searchCriteria = $this->_objectManager->create('\\Magento\\Framework\\Api\\SearchCriteria');
                $searchCriteria->setFilterGroups([$filterGroup]);
                $collection = $this->_orderRepository->getList($searchCriteria);
                $orders = [];
                foreach ($collection as $order) {
                    $orders[$order->getEntityId()] = $order;
                }
                foreach ($dataSource['data']['items'] as &$item) {
                    if (isset($orders[$item['entity_id']])) {
                        if ($orders[$item['entity_id']]->getPickupDatetime() != null) {
                            $item[$this->getData('name')] = date('Y-m-d H:i:s', strtotime($orders[$item['entity_id']]->getPickupDatetime()));
                        } else {
                            $item[$this->getData('name')] = "";
                        }
                    }
                }
            }
            return $dataSource;
        } catch (\Exception $e) {
            return $dataSource;
        }
    }
}