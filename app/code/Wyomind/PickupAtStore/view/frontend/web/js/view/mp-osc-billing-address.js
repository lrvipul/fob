/*
 * Copyright (c) 2019. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

define(
        [
            'jquery',
            'ko',
            'Magento_Checkout/js/view/billing-address',
            'Magento_Checkout/js/model/quote',
            'Magento_Checkout/js/checkout-data',
            'Mageplaza_Osc/js/model/osc-data',
            'Magento_Checkout/js/action/create-billing-address',
            'Magento_Checkout/js/action/select-billing-address',
            'Magento_Customer/js/model/customer',
            'Magento_Checkout/js/action/set-billing-address',
            'Magento_Checkout/js/model/address-converter',
            'Magento_Checkout/js/model/payment/additional-validators',
            'Magento_Ui/js/model/messageList',
            'Magento_Checkout/js/model/checkout-data-resolver',
            'Mageplaza_Osc/js/model/address/auto-complete',
            'uiRegistry',
            'mage/translate',
            'rjsResolver'
        ],
        function ($,
                ko,
                Component,
                quote,
                checkoutData,
                oscData,
                createBillingAddress,
                selectBillingAddress,
                customer,
                setBillingAddressAction,
                addressConverter,
                additionalValidators,
                globalMessageList,
                checkoutDataResolver,
                addressAutoComplete,
                registry,
                $t,
                resolver) {
            'use strict';

            var mixin = {
                initFields: function () {
                    var self = this, fieldsetName = 'checkout.steps.shipping-step.billingAddress.billing-address-fieldset';

                    setTimeout(function () {
                        var elements = registry.async(fieldsetName)().elems();
                        $.each(elements, function (index, elem) {
                            self.bindHandler(elem);
                        });
                    }.bind(this), 3000);

                    return this;
                },
            };

            return function (target) {
                return target.extend(mixin);
            };
        }
);
