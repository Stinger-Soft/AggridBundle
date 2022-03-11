/**
 * All formatters are called after getters and before renderers.
 *
 * Renderers differentiate from Formatters as they should
 * return an HTML element, whereas formatters should only
 * alter the value.
 */
(function (factory) {
    "use strict";

    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery'], function (jQuery) {
            return factory(jQuery, window, document);
        });
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, jQuery) {
            if (!root) {
                // CommonJS environments without a window global must pass a
                // root. This will give an error otherwise
                root = window;
            }

            if (!jQuery) {
                jQuery = typeof window !== 'undefined' ? // jQuery's factory checks for a global window
                    require('jquery') :
                    require('jquery')(root);
            }
            return factory(jQuery, root, root.document);
        };
    } else {
        // Browser
        factory(jQuery, window, document);
    }
}
(function (jQuery, window, document, undefined) {

    /**
     *
     * @return {string}
     */
    StingerSoftAggrid.Filter.UserFilter = function (filterParams) {
        return function (params) {
            if (params.value !== "" && typeof params.value !== "undefined" && params.value !== null) {
                return params.value.realNameAndUsername;
            }
        };
    };

}));