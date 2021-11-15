<?php

/*
 * Copyright Â© 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Model;

/**
 * Class Assignation
 * @package Wyomind\AdvancedInventory\Model
 */
class Assignation extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var array
     */
    public $_itemRegistry = [];
    /**
     * @var null
     */
    public $log = null;
    /**
     * @var null
     */
    public $order = null;
    /**
     * @var StockFactory
     */
    public $_stockFactory;
    /**
     * @var \Wyomind\PointOfSale\Model\PointOfSaleFactory
     */
    public $_posFactory;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    public $_orderFactory;
    /**
     * @var \Magento\Sales\Model\Order\AddressFactory
     */
    public $_modelAddressFactory;
    /**
     * @var ResourceModel\Order\Item\CollectionFactory
     */
    public $_orderItemCollectionFactory;
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    public $_regionFactory;
    /**
     * @var \Wyomind\AdvancedInventory\Logger\Logger|null
     */
    public $_logger = null;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Wyomind\AdvancedInventory\Model\StockFactory $stockFactory, \Wyomind\PointOfSale\Model\PointOfSaleFactory $posFactory, \Magento\Sales\Model\OrderFactory $orderFactory, \Wyomind\AdvancedInventory\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory, \Magento\Sales\Model\Order\AddressFactory $modelAddressFactory, \Magento\Directory\Model\RegionFactory $regionFactory, \Wyomind\AdvancedInventory\Logger\Logger $logger, \Magento\Framework\Model\ResourceModel\AbstractResource $abstractResource = null, \Magento\Framework\Data\Collection\AbstractDb $abstactDb = null, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_stockFactory = $stockFactory;
        $this->_posFactory = $posFactory;
        $this->_orderFactory = $orderFactory;
        $this->_modelAddressFactory = $modelAddressFactory;
        $this->_regionFactory = $regionFactory;
        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
        parent::__construct($context, $registry, $abstractResource, $abstactDb, $data);
        $this->_logger = $logger;
    }
    /**
     *
     */
    public function _construct()
    {
        $this->_init('Wyomind\\AdvancedInventory\\Model\\ResourceModel\\Assignation');
    }
    /**
     * @param $method
     * @param $rules
     * @return string|null
     */
    public function getAssignationRules($method, $rules)
    {
        switch ($method) {
            case 0:
                return null;
            case 1:
                return '*';
            case 2:
                return $rules;
        }
    }
    /**
     * @param $stringrules
     * @return array
     */
    public function getRules($stringrules)
    {
        return explode("
", $stringrules);
    }
    /**
     * @param $addressFilter
     * @param $address
     * @return bool
     */
    public function addressMatch($addressFilter, $address)
    {
        $excluding = false;
        $addressFilter = trim($addressFilter);
        $addressFilter = str_replace(['\\(', '\\)', '\\,'], ['__opening_parenthesis__', '__closing_parenthesis__', '__comma__'], $addressFilter);
        if ($addressFilter == '*') {
            $this->log .= '      country code ' . $address['country_code'] . ' matches' . "\r
";
            return true;
        }
        $result = [];
        if (preg_match('#\\* *- *\\((.*)\\)#s', $addressFilter, $result)) {
            $addressFilter = $result[1];
            $excluding = true;
        }
        $tmpAddressFilterArray = explode(',', trim($addressFilter));
        $concat = false;
        $concatened = '';
        $addressFilterArray = [];
        $i = 0;
        $countTmpAddressFilterArray = count($tmpAddressFilterArray);
        foreach ($tmpAddressFilterArray as $addressFilter) {
            if ($concat) {
                $concatened .= ',' . $addressFilter;
            } else {
                if ($i < $countTmpAddressFilterArray - 1 && preg_match('#\\(#', $addressFilter)) {
                    $concat = true;
                    $concatened .= $addressFilter;
                } else {
                    $addressFilterArray[] = $addressFilter;
                }
            }
            if (preg_match('#\\)#', $addressFilter)) {
                $addressFilterArray[] = $concatened;
                $concatened = '';
                $concat = false;
            }
            $i++;
        }
        foreach ($addressFilterArray as $addressFilter) {
            $addressFilter = trim($addressFilter);
            if (preg_match('#([A-Z]{2}) *(-)? *(?:\\( *(-)? *(.*)\\))?#s', $addressFilter, $result)) {
                $countryCode = $result[1];
                if ($address['country_code'] == $countryCode) {
                    $this->log .= '      country code ' . $address['country_code'] . ' matches' . "\r
";
                    if (!isset($result[4]) || $result[4] == '') {
                        return !$excluding;
                    } else {
                        $regionCodes = explode(',', $result[4]);
                        $inArray = false;
                        $countRegionCodes = count($regionCodes);
                        for ($i = $countRegionCodes; --$i >= 0;) {
                            $code = trim(str_replace(['__opening_parenthesis__', '__closing_parenthesis__', '__comma__'], ['(', ')', ','], $regionCodes[$i]));
                            $regionCodes[$i] = $code;
                            if ($address['region_code'] === $code) {
                                $this->log .= '      region code ' . $address['region_code'] . ' matches' . "\r
";
                                $inArray = true;
                            } elseif ($address['postcode'] === $code) {
                                $this->log .= '      postcode ' . $address['postcode'] . ' matches' . "\r
";
                                $inArray = true;
                            } elseif (mb_substr($code, 0, 1) == '/' && mb_substr($code, mb_strlen($code) - 1, 1) == '/' && @preg_match($code, $address['postcode'])) {
                                $this->log .= '      postcode ' . $address['postcode'] . ' matches ' . htmlentities($code) . "\r
";
                                $inArray = true;
                            } elseif (strpos($code, '*') !== false && preg_match('/^' . str_replace('*', '(?:.*)', $code) . '$/', $address['postcode'])) {
                                $this->log .= '      postcode ' . $address['postcode'] . ' matches ' . htmlentities($code) . "\r
";
                                $inArray = true;
                            }
                            if ($inArray) {
                                break;
                            }
                        }
                        if (!$inArray) {
                            $this->log .= '      region code ' . $address['region_code'] . ' and postcode' . $address['postcode'] . ' don\'t match' . "\r
";
                        }
                        // Vérification stricte
                        $excludingRegion = $result[2] == '-' || $result[3] == '-';
                        if ($excludingRegion && !$inArray || !$excludingRegion && $inArray) {
                            return !$excluding;
                        }
                    }
                } else {
                    $this->log .= '      country code ' . $address['country_code'] . ' doesn\'t matches' . "\r
";
                }
            }
        }
        return $excluding;
    }
    /**
     * @param $productId
     * @param $placeId
     * @param $qtyToAssign
     * @param $itemId
     * @return array
     */
    public function checkAvailability($productId, $placeId, $qtyToAssign, $itemId)
    {
        $inventory = $this->_stockFactory->create()->getStockSettings($productId, $placeId, [], $itemId);
        $this->log .= "* * * * * Checking availability, " . $qtyToAssign . " to assign" . ", " . (float) $inventory['quantity_in_stock'] . " in stock \r
";
        $qty = $inventory->getQuantityInStock() - $qtyToAssign;
        $remainingQtyAssign = $qtyToAssign;
        $qtyAssigned = 0;
        $mutipleAssignation = $this->_framework->getStoreConfig("advancedinventory/settings/multiple_assignation_enabled") == 1;
        $this->log .= "* * * * * * ";
        if (!$inventory->getManagedAtStockLevel()) {
            $this->log .= "Qty management disabled!\r
";
            $remainingQtyAssign = 0;
            $qtyAssigned = $qtyToAssign;
            $status = 4;
        } elseif ($qty >= 0) {
            $this->log .= "Qty is available!\r
";
            $remainingQtyAssign = 0;
            $qtyAssigned = $qtyToAssign;
            $status = 3;
        } elseif ($inventory->getBackorderableAtStockLevel()) {
            $this->log .= "Backorder allowed!\r
";
            $remainingQtyAssign = 0;
            $qtyAssigned = $qtyToAssign;
            $status = 2;
        } elseif ($inventory->getQuantityInStock() > 0 && $qty < 0) {
            if ($mutipleAssignation) {
                $this->log .= "Qty is partially available!\r
";
                $remainingQtyAssign = $qtyToAssign - $inventory->getQuantityInStock();
                $qtyAssigned = $inventory->getQuantityInStock();
                $status = 1;
            } else {
                $this->log .= "Qty is not completely available!\r
";
                $status = 1;
            }
        } else {
            $this->log .= "Qty is not available!\r
";
            $status = 0;
        }
        return ["status" => $status, "remaining_qty_to_assign" => $remainingQtyAssign, "qty_assigned" => $this->_helperData->qtyFormat($qtyAssigned, $inventory->getIsQtyDecimal())];
    }
    /**
     * @param $productId
     * @param $placeIds
     * @param $qtyToAssign
     * @param $itemId
     * @return array
     */
    public function checkAvailabilityPos($productId, $placeIds, $qtyToAssign, $itemId)
    {
        $inventory = $this->_stockFactory->create()->getStockSettings($productId, false, $placeIds, $itemId);
        $qtyInStock = 0;
        $backorders = false;
        foreach ($placeIds as $id) {
            $qtyInStock += $inventory->getData('quantity_' . $id);
            $backorders |= $inventory->getData('backorders_' . $id);
        }
        $this->log .= "* * * * * Checking availability, " . $qtyToAssign . " to assign" . ", " . $qtyInStock . " in stock \r
";
        $qty = $qtyInStock - $qtyToAssign;
        $remainingQtyAssign = $qtyToAssign;
        $qtyAssigned = 0;
        $mutipleAssignation = true;
        $this->log .= "* * * * * * ";
        if (!$inventory->getManagedAtStockLevel()) {
            $this->log .= "Qty management disabled!\r
";
            $remainingQtyAssign = 0;
            $qtyAssigned = $qtyToAssign;
            $status = 4;
        } elseif ($qty >= 0) {
            $this->log .= "Qty is available!\r
";
            $remainingQtyAssign = 0;
            $qtyAssigned = $qtyToAssign;
            $status = 3;
        } elseif ($backorders) {
            $this->log .= "Backorder allowed!\r
";
            $remainingQtyAssign = 0;
            $qtyAssigned = $qtyToAssign;
            $status = 2;
        } elseif ($qtyInStock > 0 && $qty < 0) {
            if ($mutipleAssignation) {
                $this->log .= "Qty is partially available!\r
";
                $remainingQtyAssign = $qtyToAssign - $qtyInStock;
                $qtyAssigned = $inventory->getQuantityInStock();
                $status = 1;
            } else {
                $this->log .= "Qty is not completely available!\r
";
                $status = 1;
            }
        } else {
            $this->log .= "Qty is not available!\r
";
            $status = 0;
        }
        return ["status" => $status, "remaining_qty_to_assign" => $remainingQtyAssign, "qty_assigned" => $this->_helperData->qtyFormat($qtyAssigned, $qtyInStock)];
    }
    /**
     * @param $entityId
     * @param bool $usePreAssignation
     * @return array
     * @throws \Exception
     */
    public function run($entityId, $usePreAssignation = true)
    {
        if ($this->order == null) {
            $order = $this->_orderFactory->create()->load($entityId);
        } else {
            $order = $this->order;
        }
        $assignTo = ["place_ids" => []];
        $this->log .= "\r
-----------------------------------------------------------------------------\r
";
        $this->log .= "------------Start assignation process for order #" . $order->getIncrementId() . " ------------------\r
";
        $this->log .= "-----------------------------------------------------------------------------\r
\r
";
        $this->log .= "Shipping method : " . $order->getShippingMethod() . "\r
\r
";
        if ($order->getShippingAddress()) {
            $shippingId = $order->getShippingAddress()->getId();
            $address = $this->_modelAddressFactory->create()->load($shippingId);
        } else {
            $address = null;
        }
        if (strstr($order->getShippingMethod(), "pickupatstore") !== false) {
            $orderedItems = $this->getAssignationByOrderId($entityId);
            $placeId = str_replace("pickupatstore_pickupatstore_", "", $order->getShippingMethod());
            $this->log .= "* * * * * Trying to assign to pos ID : " . $placeId . "\r
\r
";
            $place = $this->_posFactory->create()->load($placeId);
            if ($place->getManageInventory() == 2) {
                $destination['country_code'] = $address->getCountryId();
                $regions = $this->_regionFactory->create();
                $destination['region_code'] = $regions->load($address->getRegionId())->getCode();
                $destination['postcode'] = $address->getPostcode();
                $this->log .= "Shipped to : " . $destination['country_code'] . ',' . $destination['region_code'] . ',' . $destination['postcode'] . "\r
\r
";
                $places = $this->_posFactory->create()->getPlacesByIds(explode(',', $place->getWarehouses()));
                $orderedItems = $this->getAssignationByOrderId($entityId);
                foreach ($orderedItems->getData() as $k => $item) {
                    $qtyToAssign = $item["qty_to_assign"];
                    $this->log .= "\r
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\r
" . "* Checking availability for : " . $item['name'] . " [ SKU " . $item['sku'] . " ] [ ID " . $item["product_id"] . " ], Ordered Qty : " . $item["qty_to_assign"] . "\r
";
                    foreach ($places as $place) {
                        $this->log .= ". . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .\r
";
                        $this->log .= "* * Checking warehouse : " . $place->getName() . " [" . $place->getStoreCode() . "]\r
";
                        $rules = $this->getAssignationRules($place->getUseAssignationRules(), $place->getInventoryAssignationRules());
                        foreach ($this->getRules($rules) as $rule) {
                            $this->log .= "* * * Checking rule '" . trim($rule) . "' \r
";
                            if ($rule == '*' || $this->addressMatch($rule, $destination)) {
                                $this->log .= "* * * * This rule matches!\r
";
                                $available = $this->checkAvailability($item["product_id"], $place->getPlaceId(), $qtyToAssign, $item['item_id']);
                                if ($qtyToAssign < 1) {
                                    continue 2;
                                }
                                $qtyToAssign = $available['remaining_qty_to_assign'];
                                if ($available['status'] >= 1) {
                                    $this->log .= "* * * * * * * Assign to warehouse ID : " . $place->getPlaceId() . ", Qty assigned = " . $available['qty_assigned'] . "\r
\r
";
                                    $assignTo["place_ids"][] = $place->getPlaceId();
                                    $assignTo["items"][$item["item_id"]]["pos"][$place->getPlaceId()]["qty_assigned"] = $available['qty_assigned'];
                                    $assignTo["items"][$item["item_id"]]["product_id"] = $item["product_id"];
                                    continue 2;
                                }
                                continue 1;
                            } else {
                                $this->log .= "* * * * This rule doesn't match!\r
";
                            }
                        }
                    }
                }
            } else {
                $this->log .= "* * * * * Assign to warehouse ID : " . $placeId . "\r
\r
";
                foreach ($orderedItems->getData() as $item) {
                    $qtyToAssign = $item["qty_to_assign"];
                    $this->log .= "* * * * * * * Assign to warehouse ID : " . $placeId . ", Qty assigned = " . $qtyToAssign . "\r
\r
";
                    $assignTo["items"][$item["item_id"]]["pos"][$placeId]["qty_assigned"] = $qtyToAssign;
                    $assignTo["items"][$item["item_id"]]["product_id"] = $item["product_id"];
                }
                $assignTo["place_ids"][0] = $placeId;
            }
            /*} elseif ($address == null) {
              $this->log .= "* * * * * Not Assigned (no shipping address) \r\n\r\n";*/
        } else {
            if ($address !== null) {
                $destination['country_code'] = $address->getCountryId();
                $regions = $this->_regionFactory->create();
                $destination['region_code'] = $regions->load($address->getRegionId())->getCode();
                $destination['postcode'] = $address->getPostcode();
                $this->log .= "Shipped to : " . $destination['country_code'] . ',' . $destination['region_code'] . ',' . $destination['postcode'] . "\r
\r
";
            } else {
                $this->log .= "* * * * * No shipping address (rules won't be applied for automatic assignation) \r
\r
";
            }
            $places = $this->_posFactory->create()->getPlacesByStoreId($order->getStoreId());
            $orderedItems = $this->getAssignationByOrderId($entityId);
            if ($this->_framework->getStoreConfig("advancedinventory/settings/multiple_assignation_enabled") == 1) {
                foreach ($orderedItems->getData() as $k => $item) {
                    $qtyToAssign = $item["qty_to_assign"];
                    $this->log .= "\r
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\r
" . "* Checking availability for : " . $item['name'] . " [ SKU " . $item['sku'] . " ] [ ID " . $item["product_id"] . " ], Ordered Qty : " . $item["qty_to_assign"] . "\r
";
                    if ($item["multistock_enabled"]) {
                        if ($usePreAssignation) {
                            $preAssigned = false;
                            if (isset($item["pre_assignation"]) || $item["pre_assignation"] === null) {
                                if ($item["pre_assignation"] === null) {
                                    // par default
                                    $preAssigned = true;
                                    $this->log .= "\r
 * * * Pre Assignation found! (null)\r
";
                                    $defaultAssignation = $this->_framework->getStoreConfig("advancedinventory/settings/default_assignation_admin_order", $order->getStoreId());
                                    $this->log .= "\r
 * * * * Assignation to " . $defaultAssignation . " (default assignation from config)\r
";
                                    if ($defaultAssignation === "-1" || $defaultAssignation === -1) {
                                        // None
                                        $preAssigned = true;
                                    } elseif ($defaultAssignation === "-2" || $defaultAssignation === -2) {
                                        //Automatic
                                        $preAssigned = false;
                                    } else {
                                        $assignTo["place_ids"][] = $defaultAssignation;
                                        $assignTo["items"][$item["item_id"]]["pos"][$defaultAssignation]["qty_assigned"] = $qtyToAssign;
                                        $assignTo["items"][$item["item_id"]]["product_id"] = $item["product_id"];
                                        $preAssigned = true;
                                    }
                                } elseif ($item["pre_assignation"] === "-1" || $item["pre_assignation"] === -1) {
                                    // none
                                    $preAssigned = true;
                                    $this->log .= "\r
 * * * Pre Assignation found! (-1)\r
";
                                    $this->log .= "\r
 * * * * * No assignation required\r
";
                                } elseif ($item["pre_assignation"] !== "-2" && $item["pre_assignation"] !== -2) {
                                    // !automatic
                                    $preAssigned = true;
                                    $this->log .= "\r
 * * * Pre Assignation found! (" . $item["pre_assignation"] . ")\r
";
                                    $this->log .= "\r
 * * * * Assignation to " . $item["pre_assignation"] . "\r
";
                                    $assignation = $item["pre_assignation"];
                                    $assignTo["place_ids"][] = $assignation;
                                    $assignTo["items"][$item["item_id"]]["pos"][$assignation]["qty_assigned"] = $qtyToAssign;
                                    $assignTo["items"][$item["item_id"]]["product_id"] = $item["product_id"];
                                }
                                // 0 = automatic
                                if ($preAssigned) {
                                    continue;
                                }
                            }
                        }
                        if ($this->_framework->getDefaultConfig("advancedinventory/settings/autoassign_order")) {
                            foreach ($places as $place) {
                                $this->log .= ". . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .\r
";
                                $this->log .= "* * Checking warehouse : " . $place->getName() . " [" . $place->getStoreCode() . "]\r
";
                                $rules = $this->getAssignationRules($place->getUseAssignationRules(), $place->getInventoryAssignationRules());
                                foreach ($this->getRules($rules) as $rule) {
                                    if ($address == null) {
                                        $this->log .= "* * * * * No shipping address : rule = * \r
\r
";
                                        $rule = '*';
                                    }
                                    $this->log .= "* * * Checking rule '" . trim($rule) . "' \r
";
                                    if ($rule == '*' || $this->addressMatch($rule, $destination)) {
                                        $this->log .= "* * * * This rule matches!\r
";
                                        $available = $this->checkAvailability($item["product_id"], $place->getPlaceId(), $qtyToAssign, $item['item_id']);
                                        if ($qtyToAssign < 1) {
                                            continue 2;
                                        }
                                        $qtyToAssign = $available['remaining_qty_to_assign'];
                                        if ($available['status'] >= 1) {
                                            $this->log .= "* * * * * * * Assign to warehouse ID : " . $place->getPlaceId() . ", Qty assigned = " . $available['qty_assigned'] . "\r
\r
";
                                            $assignTo["place_ids"][] = $place->getPlaceId();
                                            $assignTo["items"][$item["item_id"]]["pos"][$place->getPlaceId()]["qty_assigned"] = $available['qty_assigned'];
                                            $assignTo["items"][$item["item_id"]]["product_id"] = $item["product_id"];
                                            continue 2;
                                        }
                                        continue 1;
                                    } else {
                                        $this->log .= "* * * * This rule doesn't match!\r
";
                                    }
                                }
                            }
                        }
                    } else {
                        $this->log .= "* * Checking " . $item['name'] . " [ SKU " . $item['sku'] . " ] [ ID " . $item["product_id"] . " ], Ordered Qty : " . $item["qty_to_assign"] . "\r
";
                        $this->log .= "* * Multi-stock is disabled\r
";
                        $qtyToAssign = 0;
                        $inventory = $this->_stockFactory->create()->getStockSettings($item["product_id"]);
                        $stockItem = $this->_stockRegistry->getStockItem($item["product_id"], "product_id");
                        if ($stockItem->getManageStock()) {
                            $newQty = $inventory->getQty() - $item["qty_to_assign"];
                            $stock = $this->_stockHelper->getStockItem($item['product_id']);
                            $journal = $this->_journalHelper;
                            $this->_journalHelper->insertRow($journal::SOURCE_PURCHASE, $journal::ACTION_QTY, "O#{$entityId},P#" . $item['product_id'], ["from" => $stock->getQty(), "to" => $newQty]);
                            $stockRegistry = $this->_stockRegistry->getStockItem($item['product_id'], "product_id");
                            $stockRegistry->setQty($newQty)->save();
                            $this->log .= "* * * Stock decremented (Qty : {$newQty})\r
";
                        } else {
                            $this->log .= "* * * Stock is not managed\r
";
                        }
                    }
                    if ($qtyToAssign) {
                        $assignTo["place_ids"][] = 0;
                    }
                }
                $this->log .= "\r
-----------------------------------------------------------------------------\r
";
                $this->log .= "\r
-----------------------------------------------------------------------------\r
";
            } else {
                $countOrderedItems = count($orderedItems->getData());
                // check pre assignation
                $preAssigned = false;
                if ($countOrderedItems && $usePreAssignation) {
                    $this->log .= "\r
###### Checking Pre-assignation\r
";
                    $firstItem = $orderedItems->getData()[0];
                    if (isset($firstItem["pre_assignation"]) || $firstItem["pre_assignation"] === null) {
                        if ($firstItem["pre_assignation"] === null) {
                            // par default
                            $preAssigned = true;
                            $this->log .= "\r
 * * * Pre Assignation found! (null)\r
";
                            $defaultAssignation = $this->_framework->getStoreConfig("advancedinventory/settings/default_assignation_admin_order", $order->getStoreId());
                            $this->log .= "\r
 * * * * Assignation to " . $defaultAssignation . " (default assignation from config)\r
";
                            if ($defaultAssignation === "-1" || $defaultAssignation === -1) {
                                // None
                                $preAssigned = true;
                            } elseif ($defaultAssignation === "-2" || $defaultAssignation === -2) {
                                //Automatic
                                $preAssigned = false;
                            } else {
                                $assignTo["place_ids"][0] = $defaultAssignation;
                                foreach ($orderedItems->getData() as $item) {
                                    if ($item["multistock_enabled"]) {
                                        $assignTo["items"][$item["item_id"]]["pos"][$defaultAssignation]["qty_assigned"] = $item["qty_to_assign"];
                                        $assignTo["items"][$item["item_id"]]["product_id"] = $item["product_id"];
                                    }
                                }
                            }
                        } elseif ($firstItem["pre_assignation"] === "-1" || $firstItem["pre_assignation"] === -1) {
                            // none
                            $preAssigned = true;
                            $this->log .= "\r
 * * * Pre Assignation found! (-1)\r
";
                            $this->log .= "\r
 * * * * * No assignation required\r
";
                            $assignTo = ["place_ids" => [0]];
                        } elseif ($firstItem["pre_assignation"] !== "-2" && $firstItem["pre_assignation"] !== -2) {
                            // !automatic
                            $preAssigned = true;
                            $this->log .= "\r
 * * * Pre Assignation found! (" . $firstItem["pre_assignation"] . ")\r
";
                            $this->log .= "\r
 * * * * Assignation to " . $firstItem["pre_assignation"] . "\r
";
                            $assignation = $firstItem["pre_assignation"];
                            $assignTo["place_ids"][0] = $assignation;
                            foreach ($orderedItems->getData() as $item) {
                                if ($item["multistock_enabled"]) {
                                    $assignTo["items"][$item["item_id"]]["pos"][$assignation]["qty_assigned"] = $item["qty_to_assign"];
                                    $assignTo["items"][$item["item_id"]]["product_id"] = $item["product_id"];
                                }
                            }
                        } else {
                            // other case = automatic
                            $preAssigned = !$this->_framework->getDefaultConfig("advancedinventory/settings/autoassign_order");
                        }
                    }
                }
                if (!$preAssigned) {
                    // automatic
                    if (count($places) == 0) {
                        foreach ($orderedItems->getData() as $item) {
                            if (!$item["multistock_enabled"]) {
                                $this->log .= "* * Checking " . $item['name'] . " [ SKU " . $item['sku'] . " ] [ ID " . $item["product_id"] . " ], Ordered Qty : " . $item["qty_to_assign"] . "\r
";
                                $this->log .= "* * Multi-stock is disabled\r
";
                                $inventory = $this->_stockFactory->create()->getStockSettings($item["product_id"]);
                                $stockItem = $this->_stockRegistry->getStockItem($item["product_id"], "product_id");
                                if ($stockItem->getManageStock()) {
                                    $newQty = $inventory->getQty() - $item["qty_to_assign"];
                                    $stock = $this->_stockHelper->getStockItem($item['product_id']);
                                    $journal = $this->_journalHelper;
                                    $this->_journalHelper->insertRow($journal::SOURCE_PURCHASE, $journal::ACTION_QTY, "O#{$entityId},P#" . $item['product_id'], ["from" => $stock->getQty(), "to" => $newQty]);
                                    $stockRegistry = $this->_stockRegistry->getStockItem($item['product_id'], "product_id");
                                    $stockRegistry->setQty($newQty)->save();
                                    $this->log .= "* * * Stock decremented (Qty : {$newQty})\r
";
                                } else {
                                    $this->log .= "* * * Stock is not managed\r
";
                                }
                            }
                        }
                    }
                    foreach ($places as $place) {
                        $this->log .= "\r
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\r
";
                        $this->log .= "* Checking warehouse : " . $place->getName() . " [" . $place->getStoreCode() . "]\r
";
                        foreach ($orderedItems->getData() as $item) {
                            $qtyToAssign = $item["qty_to_assign"];
                            if ($item["multistock_enabled"]) {
                                $this->log .= ". . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .\r
";
                                $this->log .= "* * Checking rules for " . $item['name'] . " [ SKU " . $item['sku'] . " ] [ ID " . $item["product_id"] . " ], Ordered Qty : " . $item["qty_to_assign"] . "\r
";
                                $rules = $this->getAssignationRules($place->getUseAssignationRules(), $place->getInventoryAssignationRules());
                                foreach ($this->getRules($rules) as $rule) {
                                    $this->log .= "* * * Checking rule '" . trim($rule) . "' \r
";
                                    if ($rule == '*' || $this->addressMatch($rule, $destination, false)) {
                                        $this->log .= "* * * * This rule matches!\r
";
                                        $available = $this->checkAvailability($item["product_id"], $place->getPlaceId(), $qtyToAssign, $item['item_id']);
                                        if ($available['status'] >= 2) {
                                            $this->log .= "* * * * * * * Assign to warehouse ID : " . $place->getPlaceId() . ", Qty assigned = " . $available['qty_assigned'] . "\r
\r
";
                                            $assignTo["items"][$item["item_id"]]["pos"][$place->getPlaceId()]["qty_assigned"] = $available['qty_assigned'];
                                            $assignTo["items"][$item["item_id"]]["product_id"] = $item["product_id"];
                                            $assignTo["place_ids"][0] = $place->getPlaceId();
                                            continue 2;
                                        } else {
                                            $this->log .= "* * * * * * * Can't assign to warehouse ID : " . $place->getPlaceId() . ", Assignation cancelled\r
\r
";
                                            $assignTo = ["place_ids" => [0]];
                                            continue 3;
                                        }
                                    } else {
                                        $this->log .= "* * * * This rule doesn't match!\r
";
                                    }
                                }
                            } else {
                                $this->log .= "* * Multi-stock is disabled\r
";
                                $inventory = $this->_stockFactory->create()->getStockSettings($item["product_id"]);
                                if ($inventory->getManageStockAtProductLevel()) {
                                    $newQty = $inventory->getQty() - $item["qty_to_assign"];
                                    $this->log .= "* * * Stock decremented (Qty : {$newQty})\r
";
                                } else {
                                    $this->log .= "* * * Stock is not managed\r
";
                                }
                            }
                        }
                        if (isset($assignTo["items"]) && count($assignTo["items"]) == $countOrderedItems) {
                            $this->log .= "\r
-----------------------------------------------------------------------------\r
";
                            $this->log .= "\r
 Assignation found!\r
";
                            $this->log .= "\r
-----------------------------------------------------------------------------\r
";
                            break;
                        }
                    }
                }
            }
        }
        sort($assignTo["place_ids"]);
        $assignTo["place_ids"] = implode(",", array_unique($assignTo["place_ids"]));
        if ($this->_framework->getDefaultConfig("advancedinventory/system/log_enabled")) {
            $this->_logger->notice($this->log);
        }
        return ["inventory" => $assignTo, "log" => $this->log];
    }
    /**
     * @param $observer
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function refund($observer)
    {
        $data = $this->_requestInterface->getPost("creditmemo");
        $journal = $this->_journalHelper;
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $orderId = $observer->getEvent()->getCreditmemo()->getOrderId();
        $items = $creditmemo->getAllItems();
        foreach ($items as $item) {
            if (in_array($this->_orderItemRepository->get($item->getOrderItemId())->getProductType(), $this->_helperData->getProductTypes())) {
                $id = $this->_orderItemRepository->get($item->getOrderItemId())->getParentItemId();
                if ($id == null) {
                    $id = $item->getOrderItemId();
                }
                $placeId = $data["items"][$id]['back_to_stock'];
                if (isset($data["items"][$id]['qty'])) {
                    $qty = $data["items"][$id]['qty'];
                } else {
                    $qty = 0;
                }
                if ($placeId) {
                    $assignation = $this->getAssignationByItemIdAndPlaceId($item->getOrderItemId(), $placeId);
                    $this->_journalHelper->insertRow($journal::SOURCE_REFUND, $journal::ACTION_STOCK_QTY, "O#{$orderId},P#" . $item->getProductId() . ",W#{$placeId}", ["from" => $assignation->getQtyAssigned(), "to" => $assignation->getQtyAssigned() - $qty]);
                    if (!count($assignation->getData())) {
                        $assignation->load(0);
                        $assignation->setPlaceId($placeId);
                        $assignation->setItemId($item->getOrderItemId());
                        $assignation->setQtyReturned($qty);
                        $assignation->setQtyAssigned(0)->save();
                    } else {
                        $assignation->setQtyReturned($assignation->getQtyReturned() + $qty)->save();
                    }
                    $stockFactory = $this->_stockFactory->create()->getStockByProductIdAndPlaceId($item->getProductId(), $placeId);
                    if (!$stockFactory->getManageStock()) {
                        $place = $this->_posFactory->create()->load($placeId);
                        $this->_messageManager->addError(__("The product <b>%1</b> [%2] cannot be returned to stock <b>%3</b> [%4] because stock is disabled.", $item->getName(), $item->getSku(), $place->getName(), $place->getStoreCode()));
                    } else {
                        $stockFactory->setQuantityInStock($stockFactory->getQuantityInStock() + $qty)->save();
                    }
                    $stock = $this->_stockHelper->getStockItem($item->getProductId());
                    $stockRegistry = $this->_stockRegistry->getStockItem($item->getProductId(), "product_id");
                    $this->_journalHelper->insertRow($journal::SOURCE_REFUND, $journal::ACTION_QTY, "O#{$orderId},P#" . $item->getProductId(), ["from" => $stock->getQty(), "to" => $stock->getQty() + $qty]);
                    $stockRegistry->setQty($stock->getQty() + $qty)->save();
                    if ($this->_framework->getStoreConfig("advancedinventory/settings/auto_update_stock_status")) {
                        $inventory = $this->_stockFactory->create()->getStockSettings($item['product_id']);
                        $isInStockAfter = $inventory->getStockStatus() ? "In stock" : "Out of stock";
                        $isInStockBefore = $stock->getIsInStock() ? "In stock" : "Out of stock";
                        if ($inventory->getStockStatus() != $stock->getIsInStock()) {
                            $this->_journalHelper->insertRow($journal::SOURCE_REFUND, $journal::ACTION_IS_IN_STOCK, "O#{$orderId},P#" . $item->getProductId(), ["from" => $isInStockBefore, "to" => $isInStockAfter]);
                        }
                        $stockRegistry->setIsInStock($inventory->getStockStatus())->save();
                    }
                }
            }
        }
    }
    /**
     * @param $entityId
     * @return bool
     * @throws \Exception
     */
    public function cancel($entityId)
    {
        $journal = $this->_journalHelper;
        $items = $this->getAssignationByOrderId($entityId);
        foreach ($items->getData() as $item) {
            $productId = $item['product_id'];
            $itemId = $item['item_id'];
            $assignation = $this->getAssignationByItemId($itemId);
            $placeIds = [];
            foreach ($assignation as $line) {
                $placeId = $line->getPlaceId();
                if (!isset($placeIds[$placeId])) {
                    $placeIds[$placeId] = $line->getQtyAssigned();
                } else {
                    $placeIds[$placeId] += $line->getQtyAssigned();
                }
                $line->load($line->getId())->setQtyReturned($line->getQtyAssigned())->save();
            }
            foreach ($placeIds as $placeId => $value) {
                if ($value) {
                    $stockFactory = $this->_stockFactory->create()->getStockByProductIdAndPlaceId($item['product_id'], $placeId);
                    $this->_journalHelper->insertRow($journal::SOURCE_CANCEL, $journal::ACTION_STOCK_QTY, "O#{$entityId},P#" . $productId . ",W#{$placeId}", ["from" => $stockFactory->getQuantityInStock(), "to" => $stockFactory->getQuantityInStock() + $value]);
                    $stockFactory->setQuantityInStock($stockFactory->getQuantityInStock() + $value)->save();
                }
            }
            $stock = $this->_stockHelper->getStockItem($item['product_id']);
            if ($stock != false) {
                $stockRegistry = $this->_stockRegistry->getStockItem($item['product_id'], "product_id");
                $this->_journalHelper->insertRow($journal::SOURCE_CANCEL, $journal::ACTION_QTY, "O#{$entityId},P#" . $productId, ["from" => $stock->getQty(), "to" => $stock->getQty() + $item['qty_ordered']]);
                $stockRegistry->setQty($stock->getQty() + $item['qty_ordered'])->save();
                if ($this->_framework->getStoreConfig("advancedinventory/settings/auto_update_stock_status")) {
                    $inventory = $this->_stockFactory->create()->getStockSettings($item['product_id']);
                    $isInStockAfter = $inventory->getStockStatus() ? "In stock" : "Out of stock";
                    $isInStockBefore = $stock->getIsInStock() ? "In stock" : "Out of stock";
                    if ($inventory->getStockStatus() != $stock->getIsInStock()) {
                        $this->_journalHelper->insertRow($journal::SOURCE_CANCEL, $journal::ACTION_IS_IN_STOCK, "O#{$entityId},P#" . $productId, ["from" => $isInStockBefore, "to" => $isInStockAfter]);
                    }
                    $stockRegistry->setIsInStock($inventory->getStockStatus())->save();
                }
            }
        }
        $order = $this->_orderFactory->create()->load($entityId);
        $order->setAssignedTo(0)->save();
        $this->_journalHelper->insertRow($journal::SOURCE_CANCEL, $journal::ACTION_ASSIGNATION, "O#{$entityId}", ["from" => $order->getAssignedTo(), "to" => 0]);
        $connection = $this->_appResource->getConnection('core_write');
        $tableSog = $this->_appResource->getTableName('sales_order_grid');
        $connection->update($tableSog, ["assigned_to" => 0], "entity_id = '" . $entityId . "'");
        return true;
    }
    /**
     * @param $entityId
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function insert($entityId, $data)
    {
        $journal = $this->_journalHelper;
        if (isset($data["inventory"]['items'])) {
            foreach ($data["inventory"]['items'] as $itemId => $item) {
                $quantity = 0;
                foreach ($item["pos"] as $placeId => $pos) {
                    $update['place_id'] = $placeId;
                    $update['item_id'] = $itemId;
                    $update['id'] = null;
                    $update['qty_assigned'] = (float) $pos['qty_assigned'];
                    $quantity += $pos['qty_assigned'];
                    $this->load(null)->setData($update);
                    $stockFactory = $this->_stockFactory->create()->getStockByProductIdAndPlaceId($item['product_id'], $placeId);
                    if (!empty($stockFactory->getData())) {
                        $this->_journalHelper->insertRow($journal::SOURCE_PURCHASE, $journal::ACTION_STOCK_QTY, "O#{$entityId},P#" . $item['product_id'] . ",W#{$placeId}", ["from" => $stockFactory->getQuantityInStock(), "to" => $stockFactory->getQuantityInStock() + -$pos['qty_assigned']]);
                        $stockFactory->setQuantityInStock($stockFactory->getQuantityInStock() - $pos['qty_assigned'])->save();
                    }
                    $this->save();
                }
                $stock = $this->_stockHelper->getStockItem($item['product_id']);
                $stockRegistry = $this->_stockRegistry->getStockItem($item['product_id'], "product_id");
                $this->_journalHelper->insertRow($journal::SOURCE_PURCHASE, $journal::ACTION_QTY, "O#{$entityId},P#" . $item['product_id'], ["from" => $stock->getQty(), "to" => $stock->getQty() - $quantity]);
                $stockRegistry->setQty($stock->getQty() - $quantity)->save();
                if ($this->_framework->getStoreConfig("advancedinventory/settings/auto_update_stock_status")) {
                    $inventory = $this->_stockFactory->create()->getStockSettings($item['product_id']);
                    $isInStockAfter = $inventory->getStockStatus() ? "In stock" : "Out of stock";
                    $isInStockBefore = $stock->getIsInStock() ? "In stock" : "Out of stock";
                    if ($inventory->getStockStatus() != $stock->getIsInStock()) {
                        $this->_journalHelper->insertRow($journal::SOURCE_PURCHASE, $journal::ACTION_IS_IN_STOCK, "O#{$entityId},P#" . $item['product_id'], ["from" => $isInStockBefore, "to" => $isInStockAfter]);
                    }
                    $stockRegistry->setIsInStock($inventory->getStockStatus())->save();
                }
            }
            $order = $this->_orderFactory->create()->load($entityId);
            $this->_journalHelper->insertRow($journal::SOURCE_PURCHASE, $journal::ACTION_ASSIGNATION, "O#{$entityId}", ["from" => $order->getAssignedTo(), "to" => $data["inventory"]["place_ids"]]);
            $order->setAssignedTo($data["inventory"]["place_ids"])->save();
            $connection = $this->_appResource->getConnection('core_write');
            $tableSog = $this->_appResource->getTableName('sales_order_grid');
            $connection->update($tableSog, ["assigned_to" => $data["inventory"]["place_ids"]], "entity_id = '" . $entityId . "'");
        }
        return true;
    }
    /**
     * @param $entityId
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function update($entityId, $data)
    {
        try {
            $placeIds = [];
            $journal = $this->_journalHelper;
            foreach ($data["inventory"]['items'] as $itemId => $item) {
                $quantity = 0;
                $stockMovement = 0;
                if (isset($item["pos"])) {
                    foreach ($item["pos"] as $placeId => $pos) {
                        $assignationId = null;
                        if (!$pos['assignation_id']) {
                            $assignation = $this->getAssignationByItemIdAndPlaceId($itemId, $placeId);
                            if (isset($assignation["id"])) {
                                $assignationId = $assignation["id"];
                            }
                        } else {
                            $assignationId = $pos['assignation_id'];
                        }
                        $update['place_id'] = $placeId;
                        $update['item_id'] = $itemId;
                        $update['id'] = $assignationId;
                        $update['qty_assigned'] = (float) $pos['qty_assigned'];
                        $quantity += $pos['qty_assigned'];
                        $this->load($update['id'])->setData($update);
                        if ($pos['qty_original'] - $pos['qty_assigned'] != 0) {
                            $stockMovement += $pos['qty_original'] - $pos['qty_assigned'];
                            $stockFactory = $this->_stockFactory->create()->getStockByProductIdAndPlaceId($item['product_id'], $placeId);
                            $this->_journalHelper->insertRow($journal::SOURCE_ORDER, $journal::ACTION_STOCK_QTY, "O#{$entityId},P#" . $item['product_id'] . ",W#{$placeId}", ["from" => $stockFactory->getQuantityInStock(), "to" => $stockFactory->getQuantityInStock() + ($pos['qty_original'] - $pos['qty_assigned'])]);
                            $stockFactory->setQuantityInStock($stockFactory->getQuantityInStock() + ($pos['qty_original'] - $pos['qty_assigned']));
                            $stockFactory->save();
                        }
                        if (!in_array($placeId, $placeIds) && $update['qty_assigned'] > 0) {
                            $placeIds[] = $placeId;
                        }
                        $this->save();
                    }
                }
                if ($stockMovement != 0) {
                    $stock = $this->_stockHelper->getStockItem($item['product_id']);
                    $stockRegistry = $this->_stockRegistry->getStockItem($item['product_id'], "product_id");
                    $this->_journalHelper->insertRow($journal::SOURCE_ORDER, $journal::ACTION_QTY, "O#{$entityId},P#" . $item['product_id'], ["from" => $stock->getQty(), "to" => $stock->getQty() + $stockMovement]);
                    $stockRegistry->setQty($stock->getQty() + $stockMovement)->save();
                    if ($this->_framework->getStoreConfig("advancedinventory/settings/auto_update_stock_status")) {
                        $inventory = $this->_stockFactory->create()->getStockSettings($item['product_id']);
                        $isInStockAfter = $inventory->getStockStatus() ? "In stock" : "Out of stock";
                        $isInStockBefore = $stock->getIsInStock() ? "In stock" : "Out of stock";
                        if ($inventory->getStockStatus() != $stock->getIsInStock()) {
                            $this->_journalHelper->insertRow($journal::SOURCE_ORDER, $journal::ACTION_IS_IN_STOCK, "O#{$entityId},P#" . $item['product_id'], ["from" => $isInStockBefore, "to" => $isInStockAfter]);
                        }
                        $stockRegistry->setIsInStock($inventory->getStockStatus())->save();
                    }
                }
                if ($quantity < $item["qty_to_assign"]) {
                    $placeIds[] = 0;
                }
            }
            $order = $this->_orderFactory->create()->load($entityId);
            sort($placeIds);
            $assignedTo = implode(",", $placeIds);
            $this->_journalHelper->insertRow($journal::SOURCE_ORDER, $journal::ACTION_ASSIGNATION, "O#{$entityId}", ["from" => $order->getAssignedTo(), "to" => $assignedTo]);
            $order->setAssignedTo($assignedTo)->save();
            $connection = $this->_appResource->getConnection('core_write');
            $tableSog = $this->_appResource->getTableName('sales_order_grid');
            $connection->update($tableSog, ["assigned_to" => implode(",", $placeIds)], "entity_id = '" . $entityId . "'");
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error :" . $e->getMessage());
        }
    }
    /**
     * @param $itemId
     * @param $placeId
     * @return \Magento\Framework\DataObject
     */
    public function getAssignationByItemIdAndPlaceId($itemId, $placeId)
    {
        $collection = $this->getCollection()->addFieldToFilter("item_id", ["eq" => $itemId])->addFieldToFilter("place_id", ["eq" => $placeId]);
        return $collection->getFirstItem();
    }
    /**
     * @param $itemId
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getAssignationByItemId($itemId)
    {
        $collection = $this->getCollection()->addFieldToFilter("item_id", ["eq" => $itemId]);
        return $collection;
    }
    /**
     * @param $orderId
     * @return bool
     */
    public function getAssignationRequired($orderId)
    {
        $items = $this->getAssignationByOrderId($orderId);
        foreach ($items->getData() as $item) {
            if ($item['multistock_enabled']) {
                return true;
            }
        }
        return false;
    }
    /**
     * @param $orderId
     * @param bool $itemId
     * @return mixed
     */
    public function getAssignationByOrderId($orderId, $itemId = false)
    {
        if (!isset($this->_itemRegistry[$orderId])) {
            $collection = $this->_orderItemCollectionFactory->create()->getAssignationByOrderId($orderId, $itemId);
            $this->_itemRegistry[$orderId] = $collection;
        }
        return $this->_itemRegistry[$orderId];
    }
}