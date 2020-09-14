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

    StingerSoftAggrid.Comparator.DefaultComparator = function (valueA, valueB, nodeA, nodeB, isInverted) {
        if (valueA === null && valueB === null) {
            return 0;
        }
        if (valueA === null) {
            return 1;
        }
        if (valueB === null) {
            return -1;
        }
        if (valueA < valueB) {
            return -1;
        }
        if (valueB < valueA) {
            return 1;
        }
        return 0;
    };

    /**
     *
     * @return {number}
     * @constructor
     */
    StingerSoftAggrid.Comparator.ValueComparator = function (valueA, valueB, nodeA, nodeB, isInverted) {
        let comparableValueA = null;
        if (valueA !== null && typeof valueA !== 'undefined') {
            comparableValueA = typeof valueA === 'object' && valueA.hasOwnProperty('value') ? valueA.value : valueA;
        }
        let comparableValueB = null;
        if (valueB !== null && typeof valueB !== 'undefined') {
            comparableValueB = typeof valueB === 'object' && valueB.hasOwnProperty('value') ? valueB.value : valueB;
        }
        return StingerSoftAggrid.Comparator.DefaultComparator(comparableValueA, comparableValueB, nodeA, nodeB, isInverted);
    };

    /**
     *
     * @return {number}
     * @constructor
     */
    StingerSoftAggrid.Comparator.DisplayValueComparator = function (valueA, valueB, nodeA, nodeB, isInverted) {
        let comparableValueA = null;
        if (valueA !== null && typeof valueA !== 'undefined') {
            comparableValueA = typeof valueA === 'object' && valueA.hasOwnProperty('displayValue') ? valueA.displayValue : valueA;
        }
        let comparableValueB = null;
        if (valueB !== null && typeof valueB !== 'undefined') {
            comparableValueB = typeof valueB === 'object' && valueB.hasOwnProperty('displayValue') ? valueB.displayValue : valueB;
        }
        return StingerSoftAggrid.Comparator.DefaultComparator(comparableValueA, comparableValueB, nodeA, nodeB, isInverted);
    }

    /**
     *
     * @return {number}
     * @constructor
     */
    StingerSoftAggrid.Comparator.DateComparator = function (valueA, valueB, nodeA, nodeB, isInverted) {
        var dateA = null;
        var dateB = null;
        if (valueA !== null && valueA !== undefined && valueA.value !== null && valueA.value !== undefined) {
            dateA = valueA.value.hasOwnProperty('date') ? new Date(valueA.value.date) : null;
        }
        if (valueB !== null && valueB !== undefined && valueB.value !== null && valueB.value !== undefined) {
            dateB = valueB.value.hasOwnProperty('date') ? new Date(valueB.value.date) : null;
        }
        return StingerSoftAggrid.Comparator.DefaultComparator(dateA, dateB, nodeA, nodeB, isInverted);
    }

}));