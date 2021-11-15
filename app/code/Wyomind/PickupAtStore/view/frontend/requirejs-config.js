/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * https://www.wyomind.com
 */
var config = {
    config: {
        mixins: {

            'Magento_Checkout/js/view/shipping': {
                'Wyomind_PickupAtStore/js/view/shipping': true
            },
            'Amazon_Payment/js/view/shipping': {
                'Wyomind_PickupAtStore/js/view/amazon-shipping': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Wyomind_PickupAtStore/js/model/checkout-data-resolver': true
            },
            'Magento_Checkout/js/checkout-data': {
                'Wyomind_PickupAtStore/js/checkout-data': true
            },
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'Wyomind_PickupAtStore/js/model/shipping-save-processor/default': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Wyomind_PickupAtStore/js/view/shipping-information': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'Wyomind_PickupAtStore/js/view/billing-address' : true
            },
            'Magento_Paypal/js/order-review': {
                'Wyomind_PickupAtStore/js/order-review' : true
            },


            // Aheadworks One Step Checkout
            'Aheadworks_OneStepCheckout/js/view/place-order/aggregate-validator': {
                'Wyomind_PickupAtStore/js/view/place-order/aw-osc-aggregate-validator' : true
            },
            'Aheadworks_OneStepCheckout/js/view/shipping-method': {
                'Wyomind_PickupAtStore/js/view/aw-osc-shipping-method' : true
            },


            // Mageplaza One Step Checkout
            'Mageplaza_Osc/js/view/shipping': {
                'Wyomind_PickupAtStore/js/view/mp-osc-shipping': true
            },
            'Mageplaza_Osc/js/view/billing-address': {
                'Wyomind_PickupAtStore/js/view/mp-osc-billing-address' : true
            },

            // OneStepCheckout_Iosc
            'OneStepCheckout_Iosc/js/shipping': {
                'Wyomind_PickupAtStore/js/view/osc-iosc-shipping' : true
            },

            // SwissUp OSC
            'Swissup_Firecheckout/js/mixin/view/shipping-mixin': {
                'Wyomind_PickupAtStore/js/view/osc-swissup-shipping' : true
            }
        }
    }
};
