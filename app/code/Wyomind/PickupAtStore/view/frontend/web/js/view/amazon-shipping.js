/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Amazon_Payment/js/model/storage',
        'Magento_Checkout/js/model/shipping-rate-service',
        "Magento_Ui/js/modal/alert"
    ],
    function (
        $,
        _,
        Component,
        ko,
        customer,
        addressList,
        addressConverter,
        quote,
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformationAction,
        stepNavigator,
        modal,
        checkoutDataResolver,
        checkoutData,
        registry,
        $t,
        amazonStorage
    ) {
        'use strict';

        var mixin = {
            defaults: {
                template: 'Wyomind_PickupAtStore/shipping'
            },
            setShippingInformation: function () {

                function setShippingInformationAmazon() {
                    setShippingInformationAction().done(function () {
                        stepNavigator.next();
                    });
                }

                // set the shipping address depending of the store pickup selected (if needed)

                if (typeof (PickupAtStore) !== "undefined") {

                    if (PickupAtStore.isPASSelected()) {

                        var loginFormSelector = 'form[data-role=email-with-possible-login]';
                        var emailValidationResult = customer.isLoggedIn();

                        if (!customer.isLoggedIn()) {
                            $(loginFormSelector).validation();
                            emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                        }

                        if (!emailValidationResult) {
                            $(loginFormSelector + ' input[name=username]').focus();

                            return false;
                        }

                        // WYOMIND - PICKUP AT STORE
                        var error = false;
                        if (typeof PickupAtStore.method.store == "undefined" || PickupAtStore.method.store == "0") {
                            if (PickupAtStore.config.dropdown == "1") {
                                $('#pas-pos-selector').addClass('mage-error');
                            } else {
                                $('#error-no-pos-selected').css({"display": "block"});
                            }
                            error = true;
                        }
                        if (PickupAtStore.config.dropdown == "1") {
                            if (PickupAtStore.config.date == "1" && (typeof PickupAtStore.method.date == "undefined" || PickupAtStore.method.date == "0")) {
                                $('#pas-date-selector').addClass('mage-error');
                                error = true;
                            }
                            if (PickupAtStore.config.date == "1" && (PickupAtStore.config.time == "1" && (typeof PickupAtStore.method.time == "undefined" || PickupAtStore.method.time == "0"))) {
                                $('#pas-time-selector').addClass('mage-error');
                                error = true;
                            }
                        }
                        if (error)
                            return false;

                        var method = PickupAtStore.shippingMethods[PickupAtStore.method.store];
                        this.selectShippingMethod(method);

                        if (!customer.isLoggedIn()) {

                            var pos = PickupAtStore.places["place" + PickupAtStore.method.store];

                            var shippingAddress = quote.shippingAddress();
                            shippingAddress.countryId = pos.countryId;
                            shippingAddress.regionId = pos.regionId;
                            shippingAddress.regionCode = pos.regionCode;
                            shippingAddress.region = pos.region;
                            shippingAddress.street = new Array(
                                pos.street_1,
                                pos.street_2
                            );
                            shippingAddress.company = pos.company;
                            shippingAddress.telephone = pos.telephone;
                            shippingAddress.postcode = pos.postcode;
                            shippingAddress.city = pos.city;
                            shippingAddress.firstname = pos.firstname;
                            shippingAddress.lastname = pos.lastname;
                            shippingAddress.prefix = "";
                            shippingAddress.save_in_address_book = '0';

                            selectShippingAddress(shippingAddress);

                            quote.shippingMethod(PickupAtStore.shippingMethods[PickupAtStore.method.store]);
                        }

                        jQuery.ajax({
                            url: PickupAtStore.updateShippingMethodUrl,
                            type: 'post',
                            data: {data: PickupAtStore.method},
                            showLoader: true,
                            success: function (data) {
                                setShippingInformationAction().done(
                                    function () {
                                        stepNavigator.next();
                                    }
                                );
                            }.bind(this),
                            error: function (data) {
                            }
                        });

                    } else {

                        var error = false;

                        if ($("#checkout-step-shipping_method input[type=radio]").length == 0) {
                            error = true;
                        } else {
                            let nb_checked = 0;
                            $.each($("#checkout-step-shipping_method input[type=radio]"), function () {
                                if ($(this).val().startsWith("pickupatstore_pickupatstore_") && $(this).prop("checked")) {
                                    error = true;
                                }
                                if ($(this).prop("checked")) {
                                    nb_checked++;
                                }
                            });
                            if (nb_checked == 0) {
                                error = true;
                            }
                        }

                        if (error) {
                            alert($.mage.__("Please select a shipping method"));
                            return;
                        }


                        jQuery.ajax({
                            url: PickupAtStore.updateShippingMethodUrl,
                            type: 'post',
                            data: {data: {}},
                            showLoader: true,
                            success: function (data) {
                                if (amazonStorage.isAmazonAccountLoggedIn() && customer.isLoggedIn()) {
                                    setShippingInformationAmazon();
                                } else if (amazonStorage.isAmazonAccountLoggedIn() && !customer.isLoggedIn()) {
                                    if (this.validateGuestEmail()) {
                                        setShippingInformationAmazon();
                                    }
                                } else if (this.validateShippingInformation()) {
                                    setShippingInformationAmazon();
                                }
                            }.bind(this),
                            error: function (data) {
                            }
                        });


                    }
                }


            }
        };

        return function (target) {
            return target.extend(mixin);
        };
    }
);
