import type {ValueGetterFunc, ValueGetterParams} from "@ag-grid-community/core";
import {deepFind} from "./utils";

type StingerValueGetter = (getterParams: Record<string, any>) => ValueGetterFunc

export class StingerSoftAggridValueGetter {

     
    private static getter: Record<string, StingerValueGetter> = {}

    /**
     *
     * @param {string} getter - The name of the getter function to pull
     * @param {json} getterParams
     * @returns {*} The according getter or default to the normal formatter
     */
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    static getGetter(getter: string, getterParams: any = {}): any {
        //Default to null -> Uses the default getter
        let aggridGetter = null;
        if (getter in this.getter && typeof this.getter[getter] === 'function') {
            aggridGetter = this.getter[getter](getterParams);
        } else {
            console.warn(`Getter "${getter}" not found! Returning agGrid default function`);
        }
        return aggridGetter;
    }

     
    public static registerGetter(name: string, func: StingerValueGetter): void {
        this.getter[name] = func;
    }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function ParamsDataGetter(_getterParams: any): ValueGetterFunc {
    return function (params: ValueGetterParams) {
        return params.data;
    };
}
StingerSoftAggridValueGetter.registerGetter('ParamsDataGetter', ParamsDataGetter);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function DisplayValueGetter(_getterParams: any): ValueGetterFunc {
    return function (params: ValueGetterParams) {
        const value = deepFind(params.data, params.column.getColId());
        return value === null ? null : value.displayValue;
    };
}
StingerSoftAggridValueGetter.registerGetter('DisplayValueGetter', DisplayValueGetter);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function ValueGetter(_getterParams: any): ValueGetterFunc {
    return function (params: ValueGetterParams) {
        const value = deepFind(params.data, params.column.getColId());
        return value === null ? null : value.value;
    };
}
StingerSoftAggridValueGetter.registerGetter('ValueGetter', ValueGetter);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function PercentageValueGetter(_getterParams: any): ValueGetterFunc {
    return function (params: ValueGetterParams) {
        const value = deepFind(params.data, params.column.getColId());
        return value === null ? null : value.value * 100;
    };
}
StingerSoftAggridValueGetter.registerGetter('PercentageValueGetter', PercentageValueGetter);
