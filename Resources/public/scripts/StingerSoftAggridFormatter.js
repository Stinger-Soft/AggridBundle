/**
 * All formatters are called after getters and before renderers.
 *
 * Renderers differentiate from Formatters as they should
 * return an HTML element, whereas formatters should only
 * alter the value.
 */

/**
 *
 * @return {function(*): string}
 * @constructor
 * @param {json} formatterParams
 */
StingerSoftAggrid.Formatter.DateTimeObjectFormatter = function (formatterParams) {
    return function (params) {
        if (params.value && "date" in params.value) {
            var format = formatterParams.hasOwnProperty('dateFormat') ? formatterParams.dateFormat : 'L LTS';
            return moment(params.value.date).format(format);
        }
        return null;
    };
};
