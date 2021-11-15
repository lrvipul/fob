/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

if (typeof InventoryManager == "undefined") {
    InventoryManager = {};
}

function toInt(version) {
    var ipl = 0;
    version.split('.').forEach(function (octet) {
        ipl <<= 8;
        ipl += parseInt(octet);
    });
    return (ipl >>> 0);
}


require(["jquery", "mage/mage", "mage/translate"], function ($) {
    $(function () {

        // wait for the page to be fully loaded
        var observe = setInterval(function () {
            if ($('[name=product\\[inventory\\]\\[mage\\]]').length >= 1 && $('[name=product\\[quantity_and_stock_status\\]\\[qty\\]').length >= 1) {

                // Mage version
                var mageVersion = $('[name=product\\[inventory\\]\\[mage\\]]').val();
                if (mageVersion === undefined) {
                    mageVersion = "2.0";
                }
                var pid = $('[name=product\\[inventory\\]\\[product_id\\]]');
                if (pid.val() === "") {
                    pid.parent().parent().parent().css({"display": "none"});
                    clearInterval(observe);
                    return;
                }

                if (toInt(mageVersion) >= toInt("2.1")) { // Mage >= 2.1
                    InventoryManager = {
                        debug: false,
                        autoUpdateStockStatus: 0,
                        log: function (method, args) {
                            if (this.debug) {
                                console.log("InventoryManager says " + method + "()", args);
                            }
                        },
                        updateQty: function () {
                            this.log('updateQty', arguments);
                            var qty = 0;
                            $('div[data-index=wyomind_advanced_inventory] [name*=\\]\\[qty\\]]').each(function () {
                                if ($(this).parent().parent().prev().find('select').val() == "1") {
                                    qty += parseInt($(this).val());
                                }
                            });
                            $('[name=product\\[quantity_and_stock_status\\]\\[qty\\]]')[0].value = qty;
                            if ($('[name=product\\[quantity_and_stock_status\\]\\[qty\\]]')[1]) {
                                $('[name=product\\[quantity_and_stock_status\\]\\[qty\\]]')[1].value = qty;
                            }
                        },
                        enableMultiStock: function () {

                            this.log('enableMultiStock', arguments);
                            if ($("[name=product\\[inventory\\]\\[multistock\\]]")[0].value === "0") { // multi stock disabled
                                $('[name=product\\[quantity_and_stock_status\\]\\[qty\\]]')[0].disabled = false;
                                $('[name=product\\[quantity_and_stock_status\\]\\[qty\\]]')[1].disabled = false;
                                $('[name=product\\[stock_data\\]\\[backorders\\]]')[0].disabled = false;
                                $('[name=product\\[quantity_and_stock_status\\]\\[is_in_stock\\]]')[0].disabled = false;
                                $('[name=product\\[stock_data\\]\\[use_config_backorders\\]]')[0].disabled = false;

                                $('[name*=product\\[inventory\\]\\[pos_wh\\]]').parent().parent().css({display: 'none'});
                                $('[name*=product\\[inventory\\]\\[pos_wh\\]][type=checkbox]').parent().parent().parent().css({display: 'none'});

                            } else { // multistock enabled
                                $('[name=product\\[quantity_and_stock_status\\]\\[qty\\]]')[0].disabled = true;
                                if ($('[name=product\\[quantity_and_stock_status\\]\\[qty\\]]')[1]) {
                                    $('[name=product\\[quantity_and_stock_status\\]\\[qty\\]]')[1].disabled = true;
                                }
                                $('[name=product\\[stock_data\\]\\[backorders\\]]')[0].disabled = true;
                                $('[name=product\\[stock_data\\]\\[use_config_backorders\\]]')[0].disabled = true;
                                if (InventoryManager.autoUpdateStockStatus === "1") {
                                    $('[name=product\\[quantity_and_stock_status\\]\\[is_in_stock\\]]')[0].disabled = true;
                                    if ($('[name=product\\[quantity_and_stock_status\\]\\[is_in_stock\\]]')[1]) {
                                        $('[name=product\\[quantity_and_stock_status\\]\\[is_in_stock\\]]')[1].disabled = true;
                                    }
                                } else {
                                    $('[name=product\\[quantity_and_stock_status\\]\\[is_in_stock\\]]')[0].disabled = false;
                                    if ($('[name=product\\[quantity_and_stock_status\\]\\[is_in_stock\\]]')[1]) {
                                        $('[name=product\\[quantity_and_stock_status\\]\\[is_in_stock\\]]')[1].disabled = false;
                                    }
                                }

                                $('[name*=product\\[inventory\\]\\[pos_wh\\]]').parent().parent().css({display: 'block'});
                                $('[name*=product\\[inventory\\]\\[pos_wh\\]][type=checkbox]').parent().parent().parent().css({display: 'block'});

                                InventoryManager.updateQty();
                            }


                        },
                        showDetails: function (elt) {
                            this.log('showDetails', arguments);
                            var display = ($(elt).val() >= 1 && $(document.getElementsByName("product[inventory][multistock]")[0]).val() === "1" ? "block" : "none");
                            $(elt).parent().parent().next().css({"display": display});
                            $(elt).parent().parent().next().next().css({"display": display});
                            $(elt).parent().parent().next().next().next().css({"display": display});
                            if ($(document.getElementsByName("product[inventory][multistock]")[0]).val() === "1") {
                                InventoryManager.updateQty();
                            }
                        },
                        updateConfig: function (elt) {
                            $(elt).parent().parent().parent().prev().find("select").prop('disabled', $(elt).prop('checked'));
                        }
                    };

                    InventoryManager.autoUpdateStockStatus = $('[name=product\\[inventory\\]\\[auto_update_stock_status\\]]').val();

                    InventoryManager.enableMultiStock();

                    // initialize display
                    $("div[data-index=wyomind_advanced_inventory] [name*=\\]\\[use_config_setting_for_backorders\\]]").each(function () {
                        InventoryManager.updateConfig($(this));
                    });
                    $("div[data-index=wyomind_advanced_inventory] [name*=\\]\\[manage_stock\\]]").each(function () {
                        InventoryManager.showDetails($(this));
                    });


                    // enable/disable multi stock
                    $("[name=product\\[inventory\\]\\[multistock\\]").on('change', function () {
                        InventoryManager.enableMultiStock();
                    });
                    // display details ?
                    $("div[data-index=wyomind_advanced_inventory] [name*=\\]\\[manage_stock\\]]").on('change', function () {
                        InventoryManager.showDetails($(this));
                    });
                    // qty changed
                    $("div[data-index=wyomind_advanced_inventory] [name*=\\]\\[qty\\]]").on('change', function () {
                        InventoryManager.updateQty();
                    });
                    // checkbox use config ?
                    $("div[data-index=wyomind_advanced_inventory] [name*=\\]\\[use_config_setting_for_backorders\\]]").on('change', function () {
                        InventoryManager.updateConfig($(this));
                    });


                } else {// Magento 2.0

                    InventoryManager.debug = false;
                    InventoryManager.log = function (method, args) {
                        if (this.debug) {
                            console.log("InventoryManager says " + method + "()", args);
                        }
                    };
                    InventoryManager.updateQty = function () {

                        this.log('updateQty', arguments);
                        qty = 0;
                        $('#advancedinventory_stocks  DIV.pointofsale[visibility!=hidden] INPUT.validate-number').each(function () {
                            if ($(this).parent().parent().prev().find('select').val() == "1") {
                                qty += parseInt($(this).val());
                            }
                        });
                        $('#inventory_qty').val(qty);
                    };
                    InventoryManager.enableMultiStock = function () {

                        this.log('enableMultiStock', arguments);

                        if ($("#multistock").val() === "0") {

                            $('#qty').parent().parent().show();
                            $('#inventory_qty').prop('disabled', false);
                            $("#inventory_backorders").parent().parent().show();

                            $("#inventory_stock_availability").prop('disabled', false);


                        } else {

                            $('#qty').parent().parent().hide();
                            $('#inventory_qty').prop('disabled', true);
                            $("#inventory_backorders").parent().parent().hide();
                            if (InventoryManager.autoUpdateStockStatus == 1) {
                                $("#inventory_stock_availability").prop('disabled', true);
                            }
                            InventoryManager.updateQty();
                        }

                        $('#advancedinventory_stocks').css({display: ($("#multistock").val() === "0") ? 'none' : 'block'});


                    };
                    InventoryManager.showDetails = function (elt) {

                        this.log('showDetails', arguments);

                        $(elt).next().css({"display": ($(elt).val() === "1" ? "block" : "none")});
                        $(elt).next().css('visibility', ($(elt).val() === '0') ? 'hidden' : 'visible');
                        InventoryManager.updateQty();
                    };

                    window.onload = function () {
                        InventoryManager.enableMultiStock();
                    };
                }

                clearInterval(observe);
            }
        }, 1000);
    });
});