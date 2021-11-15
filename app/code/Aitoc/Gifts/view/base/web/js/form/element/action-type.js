

define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'mage/url'
], function ($, _, uiRegistry, select, urlBuilder) {
    'use strict';

    return select.extend({
        dependentFieldNames: [
            'index = promotion_items',
            'index = actions_apply_to_html',
            'index = discount_step',
            'index = cart_amount',
            'index = conditions'

        ],

        dependentFields : [],

        initialize: function() {
            this._super();

            if (this.getActionFromUrl(location.href)) {
                this.value(this.getActionFromUrl(location.href));
            }

            // Creating a promise that resolves when we're sure that all our dependent UI components have been loaded.
            uiRegistry.promise(this.dependentFieldNames).done(_.bind(function() {
                this.dependentFields = arguments;
            }, this));

            this.proceedVisibility(this.value());
        },

        getActionFromUrl: function (url) {
            var urlParsed = url.split('/'),
                result = '';
            $.each(urlParsed, function (index, value) {
                if (value.indexOf('action') !== -1) {
                    result = urlParsed[index + 1];
                }
            });

            return result;
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
                    if (((value.indexOf('whole_cart') === 0) || (value.indexOf('amount_cart') === 0))
                        && (this.index == 'discount_step'))
                    {
                        this.hide();
                    } else if (!(value.indexOf('amount_cart') === 0) && (this.index == 'cart_amount')) {
                        this.hide();
                    } else {
                        this.show();
                    }

                } else {
                    if ((value.indexOf('whole_cart') === 0) && (this.index == 'actions_apply_to_html')) {
                        this.visible(false);
                    } else if(value.indexOf('extra_qty') === 0 && (this.index == 'promotion_items')) {
                        this.visible(false);
                    } else if(value.indexOf('amount_cart') === 0 && (this.index == 'conditions')) {
                        this.visible(false);
                    } else {
                        this.visible(true);
                    }

                }
            });
        }
    });
});
