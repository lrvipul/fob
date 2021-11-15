define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'jquery/jquery-storageapi'
], function ($, storage) {
    'use strict';
    return function (target) {

        var cacheKey = 'checkout-data',

            /**
         * @return {*}
         */
        initData = function () {
            return {
                'selectedShippingAddress': null, //Selected shipping address pulled from persistence storage
                'shippingAddressFromData': null, //Shipping address pulled from persistence storage
                'newCustomerShippingAddress': null, //Shipping address pulled from persistence storage for customer
                'selectedShippingRate': null, //Shipping rate pulled from persistence storage
                'selectedPaymentMethod': null, //Payment method pulled from persistence storage
                'selectedBillingAddress': null, //Selected billing address pulled from persistence storage
                'billingAddressFromData': null, //Billing address pulled from persistence storage
                'newCustomerBillingAddress': null //Billing address pulled from persistence storage for new customer
            };
        },

            /**
             * @return {*}
             */
            getData = function () {
                var data = storage.get(cacheKey)();

                if ($.isEmptyObject(data)) {
                    data = $.initNamespaceStorage('mage-cache-storage').localStorage.get(cacheKey);

                    if ($.isEmptyObject(data)) {
                        data = initData();
                    }
                }

                return data;
            };

        target.getShippingAddressFromData = function () {
            if (getData().selectedShippingRate !== null && getData().shippingAddressFromData !== null && getData().selectedShippingRate.startsWith('pickupatstore')) {
                var data = getData().shippingAddressFromData;
                data.lastname = "";
                data.firstname = "";
                data.city = "";
                data.country_id = "";
                data.postcode = "";
                data.street = {"0": "", "1": ""};
                data.telephone = "";
                return data;
            }
            return getData().shippingAddressFromData;
        };
        return target;
    };
});