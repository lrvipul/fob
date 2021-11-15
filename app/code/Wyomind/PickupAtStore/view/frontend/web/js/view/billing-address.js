/**
 * Copyright © 2019 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
        [
            'ko',
            'Magento_Ui/js/form/form',
            'Magento_Customer/js/model/customer',
            'Magento_Customer/js/model/address-list',
            'Magento_Checkout/js/model/quote',
            'Magento_Checkout/js/action/create-billing-address',
            'Magento_Checkout/js/action/select-billing-address',
            'Magento_Checkout/js/checkout-data',
            'Magento_Checkout/js/model/checkout-data-resolver',
            'Magento_Customer/js/customer-data',
            'Magento_Checkout/js/action/set-billing-address',
            'Magento_Ui/js/model/messageList',
            'mage/translate'
        ],
        function (
                ko,
                Component,
                customer,
                addressList,
                quote,
                createBillingAddress,
                selectBillingAddress,
                checkoutData,
                checkoutDataResolver,
                customerData,
                setBillingAddressAction,
                globalMessageList,
                $t
                ) {
            'use strict';

            var mixin = {
                defaults: {
                    template: 'Wyomind_PickupAtStore/billing-address'
                }
            };

            return function (target) {
                return target.extend(mixin);
            };
        }
);
