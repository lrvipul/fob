/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
advancedInventory = {
    attributes: [],
    updateStocks: function (classname) {
        advancedInventory.attributes = [];
        advancedInventory.length = jQuery(classname).length;
        jQuery(classname).each(function () {
            if (classname == ".super-attribute-select") {
                var attr_id = jQuery(this).attr("attribute-id");
                if (attr_id == undefined &&  jQuery(this).attr("id")) {
                    attr_id = jQuery(this).attr("id").replace("attribute", "");
                }if (attr_id == undefined) {
                    attr_id = jQuery(this).attr("name").replace("super_attribute[", "").replace("]","");
                }
                option_id = jQuery(this).attr("option-selected");
                if (option_id == undefined && jQuery(this).val() != "") {
                    option_id = jQuery(this).val()
                }
                if (option_id != null && jQuery(this).val() != "")
                    advancedInventory.attributes.push({'id': attr_id, 'value': option_id});
            } else {
                attr_id = jQuery(this).attr("attribute-id");

                option_id = jQuery(this).find(".selected").attr("option-id");

                if (option_id != undefined && option_id != null)
                    advancedInventory.attributes.push({'id': attr_id, 'value': option_id});
            }
        });

        if (advancedInventory.length == advancedInventory.attributes.length) {
            jQuery(".notice").hide();
            if (typeof advancedInventoryData !== 'undefined' && typeof advancedInventoryData.stocks !== 'undefined') {
                jQuery.each(advancedInventoryData.stocks, function (i, s) {
                    found = true;
                    jQuery.each(advancedInventory.attributes, function (x, attr) {
                        eval("attribute = s.attribute" + attr.id);
                        if (attribute !== attr.value) {
                            found = false;
                        }
                    });
                    if (found) {
                        jQuery(".ai-status-message").html(advancedInventoryData.stocks[i].message);
                        jQuery.each(s.stock, function (i, pos) {

                            jQuery("#store_" + pos.store + " SPAN.units").html(pos.qty);

                            if (pos.qty < 1) {
                                jQuery("#store_" + pos.store + " SPAN.qty").hide();
                            } else {
                                jQuery("#store_" + pos.store + " SPAN.qty").show();
                                if (pos.qty > 1) {
                                    jQuery("#store_" + pos.store + " SPAN.qty SPAN.plurial").show();
                                } else {
                                    jQuery("#store_" + pos.store + " SPAN.qty SPAN.plurial").hide();
                                }
                            }
                            if (pos.message) {
                                jQuery("#store_" + pos.store + " SPAN.status").attr("class", "status " + pos.status).html(pos.message)
                            } else {
                                jQuery("#store_" + pos.store + " SPAN.status").attr("class", "status " + pos.status).html(eval("advancedInventoryData." + pos.status));
                            }

                        });
                        jQuery("#stocks-grid TR SPAN.status").each(function(i,s){

                            if(jQuery(s).html()=="-"){
                                jQuery(s).parents("TR:eq(0)").hide()
                            }
                            else{
                                jQuery(s).parents("TR:eq(0)").show()
                            }
                        })
                    }

                });
            }
        } else {
            jQuery(".maincontent .ai-status-message").html("");
            jQuery(".maincontent .notice").show();
            jQuery(".maincontent SPAN.qty").hide();
            jQuery(".maincontent SPAN.status").attr("class", "status ").html("-")

        }
    }
};

require(["jquery", "mage/mage"], function ($) {
    if (advancedInventory != undefined) {
        $(function () {
            $(document).on("change", ".super-attribute-select", function () {
                advancedInventory.updateStocks(".super-attribute-select");
            });
            $(document).on("click", ".swatch-attribute", function () {
                advancedInventory.updateStocks(".swatch-attribute");
            });
        });
    }
});
