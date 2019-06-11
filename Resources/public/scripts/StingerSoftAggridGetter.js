/**
 * All getters are called before formatters and renderers.
 *
 * Return the value for the formatter and renderer.
 */

/**
 *
 * @param params
 * @returns {Object}
 * @constructor
 */
StingerSoftAggrid.Getter.ParamsDataGetter = function(params) {
	return params.data;
};