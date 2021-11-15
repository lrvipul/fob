
define([
    'jquery',
    'Swissup_Checkout/js/scroll-to-error'
], function ($, scrollToError) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        return target.extend({
            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {


                var result = true;

                // WYOMIND - PICKUP AT STORE
                if (typeof (PickupAtStore) !== "undefined") {

                    if (PickupAtStore.isPASSelected()) {
                        if (typeof PickupAtStore.method.store == "undefined" || $('#pas-pos-selector').val() == 0 || PickupAtStore.method.store == "0") {
                            if (PickupAtStore.config.dropdown == "1") {
                                $('#pas-pos-selector').addClass('mage-error');
                            } else {
                                $('#error-no-pos-selected').css({"display": "block"});
                            }
                            result = false;
                        }
                        if (PickupAtStore.config.dropdown == "1") {
                            if (PickupAtStore.config.date == "1" && (typeof PickupAtStore.method.date == "undefined" || PickupAtStore.method.date == "0")) {
                                $('#pas-date-selector').addClass('mage-error');
                                result = false;
                            }
                            if (PickupAtStore.config.date == "1" && (PickupAtStore.config.time == "1" && (typeof PickupAtStore.method.time == "undefined" || PickupAtStore.method.time == "0"))) {
                                $('#pas-time-selector').addClass('mage-error');
                                result = false;
                            }
                        }
                    }
                }

                if (result) {
                    result = this._super();
                }

                var event = $.Event('fc:validate-shipping-information', {
                    valid: result
                });

                $('body').trigger(event);

                // try to scroll to third-party message
                setTimeout(scrollToError, 100);

                return event.valid;
            }
        });
    };
});








