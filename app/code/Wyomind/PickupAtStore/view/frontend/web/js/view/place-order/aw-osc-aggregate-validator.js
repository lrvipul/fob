define([
    'mage/utils/wrapper',
    'underscore',
    'uiRegistry'
], function (wrapper, _, registry) {

    return function (target) {

        var _validateAddresses = wrapper.wrap(target._validateAddresses, function () {
            var isValid = true,
                provider = registry.get('checkoutProvider');

            var list = [];
            if (PickupAtStore.isPASSelected) {
                list = ['checkout.paymentMethod.billingAddress'];
            } else {
                list = ['checkout.shippingAddress', 'checkout.paymentMethod.billingAddress']
            }

            _.each(list, function (query) {
                var addressComponent = registry.get(query);

                addressComponent.validate();
                if (provider.get('params.invalid')) {
                    isValid = false;
                }
            }, this);


            return isValid;
        });

        target._validateAddresses = _validateAddresses;

        return target;
    };
});
