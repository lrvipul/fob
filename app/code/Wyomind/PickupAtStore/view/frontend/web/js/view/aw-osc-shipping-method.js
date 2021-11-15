define(
    [
        'jquery',
        'ko',
        'Aheadworks_OneStepCheckout/js/view/form/form',
        'Aheadworks_OneStepCheckout/js/model/checkout-data',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/quote',
        'Aheadworks_OneStepCheckout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Aheadworks_OneStepCheckout/js/model/payment-methods-service',
        'Aheadworks_OneStepCheckout/js/model/totals-service',
        'Aheadworks_OneStepCheckout/js/model/checkout-section/cache-key-generator',
        'Aheadworks_OneStepCheckout/js/model/checkout-section/cache',
        'Aheadworks_OneStepCheckout/js/model/checkout-data-completeness-logger',
        'mage/translate'
    ],
    function (
        $,
        ko,
        Component,
        checkoutData,
        selectShippingMethodAction,
        shippingService,
        quote,
        setShippingInformationAction,
        paymentService,
        paymentMethodConverter,
        paymentMethodsService,
        totalsService,
        cacheKeyGenerator,
        cacheStorage,
        completenessLogger,
        $t
    ) {
        'use strict';

        var mixin = {
            defaults: {
                template: 'Wyomind_PickupAtStore/aw-osc-shipping-method'
            },

        };

        return function (target) {
            return target.extend(mixin);
        };
    }
);
