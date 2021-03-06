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
        define(['jquery', 'moment'], function (jQuery, moment) {
            return factory(jQuery, moment, window, document);
        });
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, moment) {
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
            if (!moment) {
                moment = require('moment');
            }
            return factory(jQuery, moment, root, root.document);
        };
    } else {
        // Browser
        factory(jQuery, moment, window, document);
    }
}
(function (jQuery, moment, window, document, undefined) {
    moment.locale(jQuery('html').attr('lang'));

    /**
     *
     * @return {function(*): string}
     * @constructor
     * @param {json} formatterParams
     */
    StingerSoftAggrid.Formatter.DateTimeObjectFormatter = function (formatterParams) {
        return function (params) {
            var value = StingerSoftAggrid.getValueFromParams(params);
            if (value) {
                var date = typeof value == "object" ? value.date : value
                var format = formatterParams.hasOwnProperty('dateFormat') ? formatterParams.dateFormat : 'L LTS';
                return moment(date).format(format);
            }
            return '';
        };
    };

     /**
     *
     * @return {function(*): string}
     * @constructor
     * @param {json} formatterParams
     */
    StingerSoftAggrid.Formatter.NullFormatter = function (formatterParams) {
        return function (params) {
            return '';
        };
    };


    /**
     *
     * @param {json} getterParams
     * @returns {Object}
     * @constructor
     */
    StingerSoftAggrid.Formatter.DisplayValueFormatter = function (formatterParams) {
        return function (params) {
            var displayValue = StingerSoftAggrid.getDisplayValueFromParams(params);
            return displayValue === null ? '' : displayValue;
        };
    };

    /**
     *
     * @param {json} getterParams
     * @returns {Object}
     * @constructor
     */
    StingerSoftAggrid.Formatter.ValueFormatter = function (formatterParams) {
        return function (params) {
            var value = StingerSoftAggrid.getValueFromParams(params);
            return value === null ? '' : value;
        };
    };

    /**
     *
     * @param {json} getterParams
     * @returns {Object}
     * @constructor
     */
    StingerSoftAggrid.Formatter.StripHtmlDisplayValueFormatter = function (formatterParams) {
        return function (params) {
            var displayValue = StingerSoftAggrid.getDisplayValueFromParams(params);
            return displayValue === null ? '' : jQuery(displayValue).text();
        };
    };

    /**
     *
     * @param {json} getterParams
     * @returns {Object}
     * @constructor
     */
    StingerSoftAggrid.Formatter.DefaultFormatter = StingerSoftAggrid.Formatter.DisplayValueFormatter;


}));