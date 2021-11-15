

define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function ($, _, uiRegistry, select) {
    'use strict';

    return select.extend({
        dependentFieldNames: [
            'index = discount_percent',
            'index = discount_amount'
        ],

        dependentFields : [],

        initialize: function() {
            this._super();

            // Creating a promise that resolves when we're sure that all our dependent UI components have been loaded.
            uiRegistry.promise(this.dependentFieldNames).done(_.bind(function() {
                this.dependentFields = arguments;
            }, this));

            this.proceedVisibility(this.value());
        },

        /**
         * Hide fields on coupon tab
         */
        onUpdate: function (value) {
            this.proceedVisibility(value);
            return this._super();
        },

        proceedVisibility: function (value) {
            $.each(this.dependentFields, function () {
                // Form elements
                if (typeof this.show === 'function') {
                    if ((value == this.visibleValue)) {
                        this.show();
                    } else {
                        this.hide();
                    }

                }
            });
        }
    });
});
