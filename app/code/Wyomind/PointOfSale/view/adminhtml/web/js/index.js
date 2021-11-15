/*
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(["jquery"], function ($) {
    "use strict";
    return {
        importCsvModal: function () {
            $('#pos-import-csv').modal({
                'type': "slide",
                'title': "Import a csv File",
                'modalClass': "mage-new-category-dialog form-inline",
                buttons: [{
                        text: 'Import File',
                        'class': 'action-primary',
                        click: function () {
                            this.importCsvFile();
                        }.bind(this)
                    }]
            });
            $("#pos-import-csv").modal("openModal");
        },
        importCsvFile: function () {
            $("#import-csv-file").find("#csv-file-error").remove();
            var input = $("#import-csv-file").find("input#csv-file");
            var csv_file = input.val();

            // file empty ?
            if (csv_file === "") {
                $("<label>", {
                    "class": "mage-error",
                    "id": "csv-file-error",
                    "text": "This is a required field"
                }).appendTo(input.parent());
                return;
            }

            // valid file ?
            if (csv_file.indexOf(".csv") < 0) {
                $("<label>", {
                    "class": "mage-error",
                    "id": "csv-file-error",
                    "text": "Invalid file type"
                }).appendTo(input.parent());
                return;
            }

            // file not empty + valid file
            $("#import-csv-file").submit();

        }
    };
});