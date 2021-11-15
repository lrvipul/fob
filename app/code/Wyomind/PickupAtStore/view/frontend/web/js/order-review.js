/*
 * Copyright (c) 2019. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'jquery/ui',
        'mage/translate',
        'mage/mage',
        'mage/validation'
    ],
    function ($, alert) {
        'use strict';
        return function (sb) {
            return $.widget('mage.orderReview', $.mage.orderReview, {
                /**
                 * Validate Order form
                 */
                _validateForm: function () {
                    if ($('select#shipping-method').val().startsWith("pickupatstore_pickupatstore_")) {
                        if (PickupAtStore.config.date === "1" && PickupAtStore.method.date == 0) {
                            $('#pas-date-selector').addClass('mage-error');
                            alert({
                                'title': 'Error',
                                'content': 'Please select a day for pickup'
                            });
                            return false;
                        }
                        if (PickupAtStore.config.time === "1" && PickupAtStore.method.time == 0) {
                            $('#pas-time-selector').addClass('mage-error');
                            alert({
                                'title': 'Error',
                                'content': 'Please select an hour for pickup'
                            });
                            return false;
                        }
                    }
                    this.element.find(this.options.agreementSelector).off('change').on('change', $.proxy(function () {
                        var isValid = this._validateForm();
                        this._updateOrderSubmit(!isValid);
                    }, this));

                    if (this.element.data('mageValidation')) {
                        return this.element.validation().valid();
                    }

                    return true;
                }
            });
        };
    }
);

