<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PickupAtStore\Helper;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Sales\Model\OrderRepository;
/**
 * Useful method for Pickup@Store
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    static $calendarColors = ["#D50000", "#F4511E", "#F6BF26", "#33B679", "#0B8043", "#039BE5", "#3F51B5", "#7986CB", "#8E24AA", "#616161", "#E67C73"];
    /**
     * @var SearchCriteriaBuilderFactory
     */
    protected $searchCriteriaBuilderFactory;
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context, SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        parent::__construct($context);
    }
    public function getOrders($pos, $dates)
    {
        asort($dates);
        $start = array_shift($dates);
        $end = array_pop($dates);
        $start = date('Y-m-d', strtotime($start));
        $end = date('Y-m-d', strtotime($end));
        //SELECT `main_table`.* FROM `sales_order` AS `main_table`
        // WHERE ((`pickup_datetime` >= '2020-09-24')) AND ((`pickup_datetime` < '2020-10-08'))
        // AND ((`status` NOT IN('fraud', 'holded', 'canceled')))
        // AND ((`pickup_store` = '541'))
        // ORDER BY main_table.entity_id DESC
        // /!\ cannot use search criteria and common collection because of frontend customer check
        //        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $select = "SELECT `main_table`.* FROM `" . $this->resource->getTableName('sales_order') . "` AS `main_table` ";
        if ($start != $end) {
            //            $this->filterBuilder->setConditionType("gteq");
            //            $this->filterBuilder->setValue($start);
            //            $this->filterBuilder->setField("pickup_datetime");
            //            $filter = $this->filterBuilder->create();
            //            $searchCriteriaBuilder->addFilter($filter);
            //            $this->filterBuilder->setConditionType("lt");
            //            $this->filterBuilder->setValue($end);
            //            $this->filterBuilder->setField("pickup_datetime");
            //            $filter = $this->filterBuilder->create();
            //            $searchCriteriaBuilder->addFilter($filter);
            $select .= "WHERE ((`pickup_datetime` >= '{$start}')) AND ((`pickup_datetime` < '{$end}')) ";
        } else {
            //            $this->filterBuilder->setConditionType("gteq");
            //            $this->filterBuilder->setValue($start);
            //            $this->filterBuilder->setField("pickup_datetime");
            //            $filter = $this->filterBuilder->create();
            //            $searchCriteriaBuilder->addFilter($filter);
            $select .= "WHERE ((`pickup_datetime` >= '{$start}')) ";
        }
        //        $this->filterBuilder->setConditionType("nin");
        //        $this->filterBuilder->setValue(["fraud", "holded", "canceled"]);
        //        $this->filterBuilder->setField("status");
        //        $filter = $this->filterBuilder->create();
        //        $searchCriteriaBuilder->addFilter($filter);
        $select .= "AND ((`status` NOT IN('fraud', 'holded', 'canceled'))) ";
        //        $this->filterBuilder->setConditionType("eq");
        //        $this->filterBuilder->setValue($pos);
        //        $this->filterBuilder->setField("pickup_store");
        //        $filter = $this->filterBuilder->create();
        //        $searchCriteriaBuilder->addFilter($filter);
        $select .= "AND ((`pickup_store` = '{$pos}')) ";
        //        $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setDirection('DESC')->create();
        //        $searchCriteriaBuilder->addSortOrder('entity_id', 'DESC');
        $select .= "ORDER BY main_table.entity_id DESC";
        //        $searchCriteria = $searchCriteriaBuilder->create();
        //        $orders = $this->orderRepository->getList($searchCriteria);
        $connection = $this->resource->getConnection('core_read');
        $orders = $connection->fetchAll($select);
        return $orders;
    }
    /**
     * Format a datetime according to the configuration of the shipping method
     * @param string $datetime
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function formatDatetime($datetime)
    {
        return $this->dateTranslate($this->_dateTime->gmtDate($this->_configHelper->getDateFormat() . ' ' . $this->_configHelper->getTimeFormat(), strtotime($datetime)));
    }
    /**
     * Format a date according to the configuration of the shipping method
     * @param string $datetime
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function formatDate($datetime)
    {
        $datetime = substr($datetime, 0, 10) . "00:00:00";
        return $this->dateTranslate($this->_dateTime->gmtDate($this->_configHelper->getDateFormat(), strtotime($datetime)));
    }
    /**
     * Translate months and days in a formatted date/time
     * @param type $datetime
     * @return type
     */
    public function dateTranslate($datetime)
    {
        $longDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $longDaysLocale = [__('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'), __('Sunday')];
        $shortDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $shortDaysLocale = [__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')];
        $longMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $longMonthsLocale = [__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December')];
        $shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $shortMonthsLocale = [__('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'), __('Jul'), __('Aug'), __('Sep'), __('Oct'), __('Nov'), __('Dec')];
        $datetime = str_replace($longDays, $longDaysLocale, $datetime);
        $datetime = str_replace($shortDays, $shortDaysLocale, $datetime);
        $datetime = str_replace($longMonths, $longMonthsLocale, $datetime);
        $datetime = str_replace($shortMonths, $shortMonthsLocale, $datetime);
        return $datetime;
    }
    /**
     * @param $places
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function removeDisallowedPlaces($places)
    {
        $this->_logger->notice("");
        $this->_logger->notice("");
        $this->_logger->notice("##### NEW QUOTE #####");
        $orderedItems = array();
        if ($this->_framework->isAdmin()) {
            $quote = $this->_backendQuote->getQuote();
        } else {
            $quote = $this->_checkoutSession->getQuote();
        }
        if (count($quote->getAllItems()) > 0) {
            foreach ($quote->getAllItems() as $i => $item) {
                $orderedItems[$item->getItemId()]['sku'] = $item->getSku();
                $orderedItems[$item->getItemId()]['id'] = $item->getProductId();
            }
        }
        $_places = [];
        $productRepository = $this->_objectManager->get('\\Magento\\Catalog\\Model\\ProductRepository');
        foreach ($places as $place) {
            $this->_logger->notice("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
            $this->_logger->notice("* Checking point of sale : " . $place->getName() . " [" . $place->getStoreCode() . "]");
            foreach ($orderedItems as $itemId => $item) {
                $this->_logger->notice(". . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .");
                $this->_logger->notice("* * Checking availability for : " . $item['sku'] . "[ID:" . $item["id"] . "]");
                try {
                    $product = $productRepository->get($item['sku']);
                } catch (\Exception $e) {
                    $product = $productRepository->getById($item['id']);
                }
                if ($product->getDisallowPickupatstore()) {
                    $this->_logger->notice("X X X X " . $item['sku'] . " not available for store pickup");
                    $this->_logger->notice("X X X X Product is disallowing pickupatstore => removing place");
                    $_places[$place->getId()] = false;
                }
            }
        }
        $newPlaces = [];
        foreach ($places as $place) {
            if (!isset($_places[$place->getId()]) || $_places[$place->getId()] !== false) {
                $newPlaces[] = $place;
            }
        }
        return $newPlaces;
    }
    /**
     * Get stores available according to Advanced Inventory
     * @param array $places
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPickupPlaces($places)
    {
        $this->_logger->notice("");
        $this->_logger->notice("");
        $this->_logger->notice("##### NEW QUOTE #####");
        $orderedItems = array();
        if ($this->_framework->isAdmin()) {
            $quote = $this->_backendQuote->getQuote();
        } else {
            $quote = $this->_checkoutSession->getQuote();
        }
        if (count($quote->getAllItems()) > 0) {
            foreach ($quote->getAllItems() as $i => $item) {
                $orderedItems[$item->getItemId()]['sku'] = $item->getSku();
                if ($item->getParentItemId() == null || !isset($orderedItems[$item->getParentItemId()])) {
                    $orderedItems[$item->getItemId()]['qty'] = $item->getQty();
                } elseif (isset($orderedItems[$item->getItemId()]) && isset($orderedItems[$item->getParentItemId()])) {
                    $orderedItems[$item->getItemId()]['qty'] = $orderedItems[$item->getParentItemId()]['qty'];
                    unset($orderedItems[$item->getParentItemId()]);
                }
                $orderedItems[$item->getItemId()]['id'] = $item->getProductId();
                $orderedItems[$item->getItemId()]['parent_item_id'] = $item->getParentItemId();
            }
        }
        $this->_logger->notice("Checking availability for quote #" . $quote->getId());
        $stockModel = $this->_objectManager->get('\\Wyomind\\AdvancedInventory\\Model\\Stock');
        $assignationModel = $this->_objectManager->get('\\Wyomind\\AdvancedInventory\\Model\\Assignation');
        $productRepository = $this->_objectManager->get('\\Magento\\Catalog\\Model\\ProductRepository');
        $_places = array();
        foreach ($places as $place) {
            $place->setData('available', 4);
            $this->_logger->notice("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
            $this->_logger->notice("* Checking warehouse : " . $place->getName() . " [" . $place->getStoreCode() . "]");
            foreach ($orderedItems as $itemId => $item) {
                $this->_logger->notice(". . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .");
                $this->_logger->notice("* * Checking availability for : " . $item['sku'] . "[ID:" . $item["id"] . "], Ordered Qty : " . $item["qty"]);
                try {
                    $product = $productRepository->get($item['sku']);
                } catch (\Exception $e) {
                    $product = $productRepository->getById($item['id']);
                }
                if ($product->getDisallowPickupatstore()) {
                    $this->_logger->notice("X X X X " . $item['sku'] . " not available for store pickup");
                    $this->_logger->notice("X X X X NO PLACE AVAILABLE");
                    return [];
                } else {
                    $this->_logger->notice("X X X X " . $item['sku'] . " available for store pickup");
                }
                if ($stockModel->isMultiStockEnabledByProductId($item["id"])) {
                    if ($place->getManageInventory() == 2) {
                        $warehouses = $place->getWarehouses();
                        $available = $assignationModel->checkAvailabilityPos($item['id'], explode(',', $warehouses), $item["qty"], $itemId);
                    } else {
                        $available = $assignationModel->checkAvailability($item['id'], $place->getPlaceId(), $item["qty"], $itemId);
                    }
                    $place->setData('available', min($place->getData('available'), $available['status']));
                    if ($available['status'] < 2) {
                        $this->_logger->notice("X X X X " . $place->getName() . " [" . $place->getStoreCode() . "] NOT added to the shipping methods");
                        continue 2;
                    }
                } else {
                    $this->_logger->notice("* * Multi-stock is not managed, Available!");
                }
            }
            $this->_logger->notice("V V V V " . $place->getName() . " [" . $place->getStoreCode() . "] added to the shipping methods");
            $_places[] = $place;
            //            }
        }
        return $_places;
    }
}