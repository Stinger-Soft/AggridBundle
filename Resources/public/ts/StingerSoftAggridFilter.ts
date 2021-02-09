export class StingerSoftAggridFilter {
    private static filter = [];

    public static getFilter = function (filter: string, filterParams) {
        //Default to null -> Uses the default formatter
        var aggridFilter = null;
        if (filter in this.filter && typeof this.filter [filter] == 'function') {
            var finalFilterParams = filterParams || {};
            aggridFilter = this.filter[filter](finalFilterParams);
        } else {
            console.warn('Filter "' + filter + '" not found! Returning agGrid default function');
        }
        return aggridFilter;
    }

    public static registerFilter(name: string, func: any) {
        this.filter[name] = func;
    }
}

export function UserFilter(filterParams) {
    return function (params) {
        if (params.value !== "" && typeof params.value !== "undefined" && params.value !== null) {
            return params.value.realNameAndUsername;
        }
    };
};
StingerSoftAggridFilter.registerFilter('UserFilter', UserFilter);