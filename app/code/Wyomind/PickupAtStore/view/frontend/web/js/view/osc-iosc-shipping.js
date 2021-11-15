/*
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
/*global define*/
define(['jquery', 'underscore'], function ($, _) {
        'use strict';

        var mixin = {
            validateShippingMethods: function () {
                if (PickupAtStore.isPASSelected()) {
                    return true;
                } else {
                    return this._super();
                }
            }
        };

        return function (target) {
            return target.extend(mixin);
        };
    }
);
