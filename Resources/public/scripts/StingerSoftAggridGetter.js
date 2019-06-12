/**
 * All getters are called before formatters and renderers.
 *
 * Return the value for the formatter and renderer.
 */

/**
 *
 * @param {json} getterParams
 * @returns {Object}
 * @constructor
 */
StingerSoftAggrid.Getter.ParamsDataGetter = function (getterParams) {
	return function (params) {
		return params.data;
	};
};