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

    StingerSoftAggrid.Editor.DatePicker = function () {
    };

// gets called once before the renderer is used
    StingerSoftAggrid.Editor.DatePicker.prototype.init = function (params) {
        // create the cell
        this.eInput = document.createElement('input');

        if (typeof params.value !== "undefined" && params.value !== null) {
            this.eInput.value = moment(params.value.date).format(moment.localeData().longDateFormat('L'));
        }

        // https://jqueryui.com/datepicker/
        jQuery(this.eInput).datepicker({
            format: moment.localeData().longDateFormat('L').toLowerCase()
        });
    };

// gets called once when grid ready to insert the element
    StingerSoftAggrid.Editor.DatePicker.prototype.getGui = function () {
        return this.eInput;
    };

// focus and select can be done after the gui is attached
    StingerSoftAggrid.Editor.DatePicker.prototype.afterGuiAttached = function () {
        this.eInput.focus();
        this.eInput.select();
    };

// returns the new value after editing
    StingerSoftAggrid.Editor.DatePicker.prototype.getValue = function () {
        if (this.eInput.value === "") {
            return null;
        }
        return {date: moment(this.eInput.value, 'L').toDate()};
    };

// any cleanup we need to be done here
    StingerSoftAggrid.Editor.DatePicker.prototype.destroy = function () {
        // but this example is simple, no cleanup, we could
        // even leave this method out as it's optional
    };

// if true, then this editor will appear in a popup
    StingerSoftAggrid.Editor.DatePicker.prototype.isPopup = function () {
        // and we could leave this method out also, false is the default
        return false;
    };

}));