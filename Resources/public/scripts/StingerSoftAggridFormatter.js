/**
 * All formatters are called after getters and before renderers.
 *
 * Renderers differentiate from Formatters as they should
 * return an HTML element, whereas formatters should only
 * alter the value.
 */

/**
 *
 * @param params
 * @returns {*}
 * @constructor
 */
StingerSoftAggrid.Formatter.DateTimeObjectFormatter = function(params) {
	if(params.value && "date" in params.value) {
		var format = params.colDef.hasOwnProperty('dateFormat') ? params.colDef.dateFormat : 'LLL';
		return moment(params.value.date).format(format);
	}
	return null;
};

/**
 * @return {string}
 */
StingerSoftAggrid.Formatter.RawHtmlFormatter = function(params) {
	return params.value ? params.value : '';
};