/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */


InventoryManager = {
    debug: true,
    log: function (method, args) {
        if (this.debug)
            console.log("InventoryManager says " + method + "()", args);
    },
    apply: function (url) {
        jQuery("FORM").attr("action", url).submit();
        this.log('apply', arguments);
    }
};


require(["jquery"], function ($) {

    $(document).on('click', '#pointofsale_tabs_inventory', function () {
        if ($("#status").val() == 1) {
            $('label[for=stock_status_message] span').html($('#stock_status_message-note span.pos_label').html());
        } else {
            $('label[for=stock_status_message] span').html($('#stock_status_message-note span.wh_label').html());
        }
    });

});