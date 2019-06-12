/**
 *
 * @return {string}
 */
StingerSoftAggrid.Filter.UserFilter = function(filterParams) {
	return function(params) {
		if (params.value !== "" && typeof params.value !== "undefined" && params.value !== null) {
			return params.value.realNameAndUsername;
		}
	};
};