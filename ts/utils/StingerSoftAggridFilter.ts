export class StingerSoftAggridFilter {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    private static filter: Record<string, any> = {};

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static getFilter = function (this: typeof StingerSoftAggridFilter, filter: string, filterParams: any = {}): any {
        //Default to null -> Uses the default formatter
        let aggridFilter = null;
        if (filter in this.filter && typeof this.filter [filter] === 'function') {
            const finalFilterParams = filterParams || {};
            aggridFilter = this.filter[filter](finalFilterParams);
        } else {
            console.warn(`Filter "${filter}" not found! Returning agGrid default function`);
        }
        return aggridFilter;
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static registerFilter(name: string, func: any): void {
        this.filter[name] = func;
    }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function UserFilter(_filterParams: any): (params: any) => string | undefined {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return function (params: any): string | undefined {
        if (params.value !== "" && typeof params.value !== "undefined" && params.value !== null) {
            return params.value.realNameAndUsername;
        }
        return undefined;
    };
}
StingerSoftAggridFilter.registerFilter('UserFilter', UserFilter);
