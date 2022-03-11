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

    StingerSoftAggrid.TextFormatter.CellRendererTextFormatter = function (formatterParams) {
        return function (value, colDef) {
            var cellRenderer = colDef.filterParams.cellRenderer;
            var cellRendererParams = colDef.filterParams.cellRendererParams;
            var displayValue = StingerSoftAggrid.Renderer.invokeRenderer(cellRenderer, cellRendererParams, value);
            if (displayValue === null || displayValue === "") {
                return value;
            }
            return displayValue;
        };
    };

    StingerSoftAggrid.TextFormatter.NullValueTextFormatter = function (formatterParams) {
        return function (value, colDef) {
            if (value === null || value === "") {
                return formatterParams.nullValueLabel || '';
            }
            return value;
        };
    };

}));
