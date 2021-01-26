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
	 * All setters are called before formatters and renderers.
	 *
	 * Return the value for the formatter and renderer.
	 */

	StingerSoftAggrid.Setter.deepSet = function (obj, path, value) {
		if (path.length === 1) {
			obj[path] = value;
			return;
		}
		StingerSoftAggrid.Setter.deepSet(obj[path[0]], path.slice(1), value);
	}

	/**
	 *
	 * @param {json} setterParams
	 * @returns {Object}
	 * @constructor
	 */
	StingerSoftAggrid.Setter.ParamsDataSetter = function (setterParams) {
		return function (params) {
			return params.data;
		};
	};

	/**
	 *
	 * @param {json} setterParams
	 * @returns {Object}
	 * @constructor
	 */
	StingerSoftAggrid.Setter.SetFilterSetter = function(setterParams) {
		var mapping = setterParams.keyValueMapping;
		var translationDomain = setterParams.translationDomain || setterParams.translation_domain || '';
		return function (params) {
			if(params.newValue === params.oldValue) {
				return false;
			}
			var newValue = params.newValue;
			var value = mapping.hasOwnProperty(newValue) ? mapping[newValue] : newValue;
			var displayValue = translationDomain !== null && translationDomain !== '' && typeof Translator !== 'undefined' ? Translator.trans(newValue, {}, translationDomain) : value;
			var finalValue = {
				value: value,
				displayValue: displayValue
			};
			StingerSoftAggrid.Setter.deepSet(params.data, params.column.colId.split('.'), finalValue);
			return true;
		};
	}

}));
