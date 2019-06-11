/**
 * All keyCreators are called for Set Filters and have to return a single string.
 *
 * Set Filters do not support objects.
 * @return {string}
 */
StingerSoftAggrid.Creator.UserCreator = function(params) {
	if(params.value !== "" && typeof params.value !== "undefined" && params.value !== null) {
		return params.value.firstname + "|" + params.value.surname;
	}
};