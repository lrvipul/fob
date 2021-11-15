<?php

namespace Wyomind\AdvancedInventory\Block\Catalog\Product;

/**
 * Class Stock
 * @package Wyomind\AdvancedInventory\Block\Catalog\Product
 */
class Stock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Wyomind\AdvancedInventory\Model\StockFactory
     */
    protected $_stockFactory;
    /**
     * @var
     */
    protected $_product;
    /**
     * @var string
     */
    protected $_cmsMapPage = 'pointofsale';
    /**
     * @var |null
     */
    protected $_storeId = null;
    /**
     * @var int|null
     */
    protected $_customerId = null;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Framework\View\Element\Template\Context $context, \Wyomind\AdvancedInventory\Model\StockFactory $stockFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
        $this->_storeId = $this->_storeManager->getStore()->getStoreId();
        $this->_stockFactory = $stockFactory;
        $this->_customerId = $this->_customerSession->getCustomerGroupId();
    }
    /**
     * @return string|void
     */
    public function getMessage()
    {
        if ($this->_framework->getStoreConfig("advancedinventory/settings/enabled")) {
            $rtn = "";
            $places = $this->_modelPos->getPlacesByStoreId($this->_storeId, true);
            $placeIds = [];
            foreach ($places as $place) {
                $placeIds[] = $place->getPlaceId();
            }
            $stocks = $this->_modelStock->getStockSettings($this->_product->getId(), false, $placeIds);
            foreach ($places as $place) {
                if (!$place->getManageInventory()) {
                    continue;
                }
                $inCustomerGroups = in_array($this->_customerId, explode(',', $place->getCustomerGroup()));
                $inStoreviews = in_array($this->_storeId, explode(',', $place->getStoreId()));
                if (!$inStoreviews || !$inCustomerGroups) {
                    continue;
                }
                if ($this->_product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    $rtn .= "<div class='ai-status-message'></div>";
                    $rtn .= "<div class='notice'>" . __("* Please configure the options to get the availability") . "</div>";
                    break;
                } else {
                    if ($place->getManageInventory() == 2) {
                        $warehouses = explode(',', $place->getWarehouses());
                        $stocks = $this->_stockFactory->create()->getStockSettings($this->_product->getId(), false, $warehouses);
                        $warehousePlaces = $this->_modelPos->getPlacesByIds($place->getWarehouses());
                        foreach ($warehousePlaces as $warehouse) {
                            $manageStock = "manage_stock_" . $warehouse->getId() . "";
                            $qty = "quantity_" . $warehouse->getId() . "";
                            $backorders = "backorders_" . $warehouse->getId();
                            if ($stocks[$manageStock] && ($stocks[$qty] > $stocks['min_qty'] || $stocks[$backorders])) {
                                $rtn .= "<div class='ai-status-message'>" . $warehouse->getStockStatusMessage() . "</div>";
                                break 2;
                            }
                        }
                    } else {
                        $backorders = "backorders_" . $place->getId() . "";
                        $isInStock = "is_in_stock_" . $place->getId() . "";
                        $manageStock = "manage_stock_" . $place->getId() . "";
                        $qty = "quantity_" . $place->getId() . "";
                        $backorders = "backorders_" . $place->getId();
                        if ($stocks[$manageStock] && ($stocks[$qty] > $stocks['min_qty'] || $stocks[$backorders])) {
                            $rtn .= "<div class='ai-status-message'>" . $place->getStockStatusMessage() . "</div>";
                            break;
                        }
                    }
                }
            }
            return $rtn;
        }
        return;
    }
    /**
     * @param bool $ajax
     * @return mixed|string|void
     */
    public function getGrid($ajax = false)
    {
        if ($this->_framework->getStoreConfig('advancedinventory/settings/enabled')) {
            $nbStoresToDisplay = $this->_framework->getStoreConfig("pointofsale/settings/display_x_first_pos");
            if (!$nbStoresToDisplay) {
                $nbStoresToDisplay = 0;
            }
            $preferredStore = $this->_cookieManager->getCookie('preferred_store');
            if (!empty($preferredStore)) {
                $preferredStore = json_decode($preferredStore);
                $preferredStoreId = $preferredStore->id;
            } else {
                $preferredStoreId = -1;
            }
            $posPlaces = $this->_cookieManager->getCookie('pos-places');
            $distances = [];
            if (!empty($posPlaces)) {
                $tmpPosStores = json_decode($posPlaces, true);
                foreach ($tmpPosStores as $s) {
                    if (isset($s['distance'])) {
                        $distances[$s['id']] = $s['distance'];
                    } else {
                        $distances[$s['id']] = [];
                    }
                }
            } else {
                $distances = [];
            }
            // Tooltip
            $tooltip = '';
            $displayTooltip = $this->_framework->getStoreConfig('advancedinventory/settings/display_tooltip', $this->_storeId);
            if ($displayTooltip) {
                $tooltipText = $this->_framework->getStoreConfig('advancedinventory/settings/tooltip_text', $this->_storeId);
                $tooltipSize = $this->_framework->getStoreConfig('advancedinventory/settings/tooltip_size', $this->_storeId);
                $tooltipPosition = $this->_framework->getStoreConfig('advancedinventory/settings/tooltip_position', $this->_storeId);
                $tooltip = '<span data-lite-tooltip-width="' . $tooltipSize . '" 
                            data-lite-tooltip-position="' . $tooltipPosition . '" 
                            data-lite-tooltip="' . htmlentities($tooltipText) . '" class="ai-tooltip"></span>';
            }
            $rtnPreferred = "
                 <thead>
                    <tr><th>" . __('Preferred Store') . "</th><th></th><th>" . __('Availability') . $tooltip . "</th></tr>
                 </thead>
                 <tbody>";
            $rtn = "<table class='data table additional-attributes'>
{{PREFERRED}}
                 <thead>
                    <tr><th>" . __('Store') . "</th><th></th><th>" . __('Availability') . $tooltip . "</th></tr>
                 </thead>
                 <tbody>";
            $places = $this->_modelPos->getPlacesByStoreId($this->_storeId, true);
            $placeIds = [];
            foreach ($places as $place) {
                $placeIds[] = $place->getPlaceId();
            }
            $stocks = $this->_modelStock->getStockSettings($this->_product->getId(), false, $placeIds);
            $newPlaces = [];
            if (!empty($posPlaces)) {
                $tmpPosStores = json_decode($posPlaces, true);
                foreach ($places as $place) {
                    if ($place->getId() == $preferredStoreId) {
                        $newPlaces[] = $place;
                    } else {
                        foreach ($tmpPosStores as $s) {
                            if ($place->getId() == $s['id']) {
                                $newPlaces[] = $place;
                            }
                        }
                    }
                }
            } else {
                foreach ($places as $place) {
                    $newPlaces[] = $place;
                }
            }
            if (count($distances)) {
                usort($newPlaces, function ($a, $b) use($distances) {
                    if (isset($distances[$a->getId()]) && isset($distances[$b->getId()]) && isset($distances[$a->getId()]['value']) && isset($distances[$b->getId()]['value'])) {
                        return $distances[$a->getId()]['value'] > $distances[$b->getId()]['value'];
                    } elseif (isset($distances[$a->getId()])) {
                        return -1;
                    } elseif (isset($distances[$b->getId()])) {
                        return 1;
                    } else {
                        return 1;
                    }
                });
            }
            $counter = 0;
            foreach ($newPlaces as $place) {
                if ($place->getManageInventory() == 0) {
                    continue;
                }
                if (empty($posPlaces) || ($nbStoresToDisplay == 0 || $counter < $nbStoresToDisplay || $preferredStoreId == $place->getId())) {
                    $inCustomerGroups = in_array($this->_customerId, explode(',', $place->getCustomerGroup()));
                    $inStoreviews = in_array($this->_storeId, explode(',', $place->getStoreId()));
                    if ($place->getStatus() != 1 || !$inStoreviews || !$inCustomerGroups) {
                        continue;
                    }
                    if ($this->_product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                        $msg = "<span id='pos_" . $place->getId() . "'>";
                        $msg .= "<span class='status in_stock'>-</span>";
                        $msg .= " <span class='qty' style='display:none;'>(<span class='units'></span> " . __("unit") . "<span class='plurial'>s</span>)</span>";
                        $msg .= "</span>";
                    } else {
                        $qty = "quantity_" . $place->getId() . "";
                        $manageStock = "manage_stock_" . $place->getId() . "";
                        $backorders = "backorders_" . $place->getId() . "";
                        $isInStock = "is_in_stock_" . $place->getId() . "";
                        $backorderAllowed = "backorder_allowed_" . $place->getId() . "";
                        if ($place->getManageInventory() == 2) {
                            $warehouses = explode(',', $place->getWarehouses());
                            $stocksWarehouses = $this->_modelStock->getStockSettings($this->_product->getId(), false, $warehouses);
                            $stocks[$qty] = 0;
                            $stocks[$isInStock] = false;
                            foreach ($warehouses as $warehouse) {
                                $stocks[$qty] += $stocksWarehouses['quantity_' . $warehouse];
                                $stocks[$isInStock] |= $stocksWarehouses['is_in_stock_' . $warehouse];
                                $stocks[$manageStock] |= $stocksWarehouses['manage_stock_' . $warehouse];
                                $stocks[$backorderAllowed] = max($stocks[$backorders], $stocksWarehouses['backorder_allowed_' . $warehouse]);
                                $stocks[$backorders] |= $stocksWarehouses['backorders_' . $warehouse];
                            }
                        }
                        if ($stocks[$manageStock] == 2) {
                            continue;
                        }
                        $msgInStock = $place->getStockStatusMessage();
                        $msgBackorder = $place->getStockStatusMessageBackorder();
                        $msgOutOfStock = $place->getStockStatusMessageOutOfStock();
                        if (!$stocks[$manageStock] || $stocks[$isInStock] != "0" && $stocks[$qty] > 0) {
                            $msg = "<span id='pos_" . $place->getId() . "'>";
                            $msg .= "<span class='status in_stock'>" . ($msgInStock != "" ? $msgInStock : __("In stock")) . "</span>";
                            if ($stocks[$manageStock]) {
                                $units = $stocks[$qty] > 1 ? __("units") : __("unit");
                                $msg .= " <span class='qty'> (<span class='units'>" . $stocks[$qty] . "</span> " . $units . ")</span>";
                            }
                            $msg .= "</span>";
                        } elseif ($stocks[$backorders]) {
                            $msg = "<span id='pos_" . $place->getId() . "'>";
                            if ($stocks[$backorderAllowed] > 1) {
                                $msg .= "<span class='status backorder'>" . ($msgBackorder != "" ? $msgBackorder : __("Backorder")) . "</span>";
                            } else {
                                $msg .= "<span class='status in_stock'>" . ($msgInStock != "" ? $msgInStock : __("In stock")) . "</span>";
                            }
                            $msg .= "</span>";
                        } else {
                            $msg = "<span id='pos_" . $place->getId() . "'>";
                            $msg .= "<span class='status out_of_stock'>" . ($msgOutOfStock != "" ? $msgOutOfStock : __("Out of stock")) . "</span>";
                            $msg .= "</span>";
                        }
                    }
                    $tmpRtn = "<tr class='ai_store' id='store_" . $place->getId() . "'>";
                    $tmpRtn .= "<td>" . $place->getName() . "</td>";
                    if (isset($distances[$place->getId()]) && isset($distances[$place->getId()]['text'])) {
                        $tmpRtn .= "<td>" . $distances[$place->getId()]['text'] . "</td>";
                    } else {
                        $tmpRtn .= "<td></td>";
                    }
                    $tmpRtn .= "<td>" . $msg . "</td>";
                    $tmpRtn .= "</tr>";
                    if ($preferredStoreId != $place->getId()) {
                        $rtn .= $tmpRtn;
                    } else {
                        $rtnPreferred .= $tmpRtn;
                    }
                    $counter++;
                }
            }
            if ($preferredStoreId != -1) {
                $rtnPreferred .= "</tr></tbody>";
            } else {
                $rtnPreferred = "";
            }
            $rtn = str_replace("{{PREFERRED}}", $rtnPreferred, $rtn);
            $rtn .= "<tr><td><a target='_blank' href='" . $this->_urlBuilder->getUrl($this->_cmsMapPage) . "'>" . __('Find the nearest store') . "</a></td>";
            if ($this->_product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $rtn .= "<td><div class='notice'>" . __("* Please configure the options to get the availability") . "</div></td>";
            } else {
                $rtn .= "<td></td>";
            }
            $rtn .= "<td></td>";
            $rtn .= "</tr></tbody></table>";
            if ($ajax) {
                return $rtn;
            } else {
                $script = "<script>";
                $script .= "require(['jquery'],function(\$) {";
                $script .= "function updateStocksGrid() {";
                $script .= "\$('#stocks-grid').html('').addClass('loader');";
                $script .= "\$.ajax({
                    url: '" . $this->getUrl('advancedinventory/update/stocks') . "',
                    data: {productId:" . $this->_product->getId() . "},
                    method: 'post',
                    global: false,
                    complete: function (response) {
                        var data = \$.parseJSON(response.responseText);
                         \$('#stocks-grid').removeClass('loader').html(data.html);
                         if (data.stocks != null && data.stocks !== '') {
                            if (typeof advancedInventoryData !== 'undefined') {
                                advancedInventoryData.stocks = jQuery.parseJSON(data.stocks);
                            }
                            advancedInventory.updateStocks('.super-attribute-select');
                            advancedInventory.updateStocks('.swatch-attribute');
                         }
                    }
                });";
                $script .= "}";
                $script .= "\$(document).on('preferred-store-selected',updateStocksGrid);";
                $script .= "});";
                $script .= "</script>";
                return $script . "<div id='stocks-grid'></div><script>require(['jquery'],function(\$) { \$(document).trigger('preferred-store-selected'); });</script>";
            }
        }
        return;
    }
    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getJson()
    {
        $stocks = $this->getDataJson();
        if ($stocks != "") {
            return '<script type="text/javascript">
                     advancedInventoryData={
                        stocks:' . $stocks . ',
                        in_stock :"' . __("In stock") . '",
                        out_of_stock:"' . __("Out of stock") . '",
                        backorder:"' . __("Backorder") . '"
                    }
                  </script>
               ';
        } else {
            return "";
        }
    }
    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDataJson()
    {
        if ($this->_framework->getStoreConfig("advancedinventory/settings/enabled")) {
            $places = $this->_modelPos->getPlacesByStoreId($this->_storeId, true);
            $placeIds = [];
            foreach ($places as $place) {
                $placeIds[] = $place->getPlaceId();
            }
            if ($this->_product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $attributes = [];
                $attributesTmp = $this->_product->getTypeInstance(true)->getConfigurableAttributes($this->_product);
                foreach ($attributesTmp as $_attribute) {
                    $attributes[] = $this->_modelEavConfig->getAttribute('catalog_product', $_attribute->getAttributeId());
                }
                $associatedProduct = $this->_product->getTypeInstance()->getUsedProducts($this->_product);
                $children = [];
                $i = 0;
                $placeIds = [];
                $places = $this->_modelPos->getPlaces();
                foreach ($places as $place) {
                    $placeIds[] = $place->getPlaceId();
                }
                foreach ($associatedProduct as $child) {
                    $stocks = $this->_modelStock->getStockSettings($child->getId(), false, $placeIds);
                    foreach ($attributes as $attr) {
                        $children[$i]["attribute" . $attr->getAttributeId()] = $child->getData($attr->getAttributeCode());
                    }
                    foreach ($places as $place) {
                        $inCustomerGroups = in_array($this->_customerId, explode(',', $place->getCustomerGroup()));
                        $inStoreviews = in_array($this->_storeId, explode(',', $place->getStoreId()));
                        if ($place->getStatus() != 1 || !$inStoreviews || !$inCustomerGroups) {
                            continue;
                        }
                        $qty = "quantity_" . $place->getId() . "";
                        $manageStock = "manage_stock_" . $place->getId() . "";
                        $backorders = "backorders_" . $place->getId() . "";
                        $backorderAllowed = "backorder_allowed_" . $place->getId() . "";
                        $isInStock = "is_in_stock_" . $place->getId() . "";
                        if ($place->getManageInventory() == 2) {
                            $warehouses = explode(',', $place->getWarehouses());
                            $stocksWarehouses = $this->_modelStock->getStockSettings($child->getId(), false, $warehouses);
                            $stocks[$isInStock] = 0;
                            $stocks[$qty] = 0;
                            $stocks[$backorderAllowed] = 0;
                            $stocks[$backorders] = false;
                            foreach ($warehouses as $warehouse) {
                                $stocks[$qty] += $stocksWarehouses['quantity_' . $warehouse];
                                $stocks[$isInStock] |= $stocksWarehouses['is_in_stock_' . $warehouse];
                                $stocks[$manageStock] |= $stocksWarehouses['manage_stock_' . $warehouse];
                                $stocks[$backorderAllowed] = max($stocks[$backorders], $stocksWarehouses['backorder_allowed_' . $warehouse]);
                                $stocks[$backorders] |= $stocksWarehouses['backorders_' . $warehouse];
                            }
                        }
                        if ($stocks[$manageStock] == 2) {
                            continue;
                        }
                        $msgInStock = $place->getStockStatusMessage();
                        $msgBackorder = $place->getStockStatusMessageBackorder();
                        $msgOutOfStock = $place->getStockStatusMessageOutOfStock();
                        $msg = "";
                        if ($stocks[$isInStock] != "0" && $stocks[$qty] > 0) {
                            $status = "in_stock";
                            $msg = $msgInStock;
                            if (!isset($children[$i]["message"])) {
                                $children[$i]["message"] = $place->getStockStatusMessage();
                            }
                        } else {
                            if ($stocks[$backorders]) {
                                if ($stocks[$backorderAllowed] > 1) {
                                    $status = "backorder";
                                    $msg = $msgBackorder;
                                } else {
                                    $status = "in_stock";
                                    $msg = $msgBackorder;
                                }
                                if (isset($children[$i]["message"])) {
                                    $children[$i]["message"] = $place->getStockStatusMessage();
                                }
                            } else {
                                $status = "out_of_stock";
                                $msg = $msgOutOfStock;
                            }
                        }
                        $children[$i]['stock'][] = ["store" => $place->getPlaceId(), "qty" => (int) $stocks[$qty] - (int) $stocks['min_qty'], "status" => $status, "message" => $msg];
                    }
                    $i++;
                }
                return $this->_jsonHelperData->jsonEncode($children);
            }
            return "";
        }
        return "";
    }
    /**
     * @param $_product
     * @param string $type
     * @param bool $includeJson
     * @param bool $ajax
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function output($_product, $type = 'grid', $includeJson = true, $ajax = false)
    {
        $this->_product = $_product;
        if ($type == "json") {
            return $this->getDataJson();
        }
        $html = "";
        if ($includeJson) {
            $html .= $this->getJson();
        }
        if ($type == "grid") {
            $html .= $this->getGrid($ajax);
        }
        if ($type == "message") {
            $html .= $this->getMessage();
        }
        return $html;
    }
}