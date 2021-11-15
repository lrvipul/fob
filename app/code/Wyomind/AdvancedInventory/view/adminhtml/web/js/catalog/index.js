/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

require([
    'jquery',
    "mage/mage",
    'Wyomind_AdvancedInventory/js/catalog/jstree.min'
], function ($) {

        $(document).on("click",'.treeview', function (e) {
            var id = $(this).attr("identifier");
            var url = $(this).attr("url");
            $(this).remove();
            
            $("#" + id).jstree({
                'core': {
                    'data': {
                        'url': url,
                        'data': function (node) {
                            return {'level': node.id};
                        }
                    }
                }
            });
        });
});