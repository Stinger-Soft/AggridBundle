/// <reference types="jquery">
import type jQuery from 'jquery';

import {StingerSoftAggrid} from "./StingerSoftAggrid";
import {ValueGetterParams} from "ag-grid-community";
import {deepFind} from "./utils";


export class StingerSoftAggridValueGetter {

    private static getter = [];

    /**
     *
     * @param {string} getter - The name of the getter function to pull
     * @param {json} getterParams
     * @returns {*} The according getter or default to the normal formatter
     */
    static getGetter(getter: string, getterParams: any[]) {
        //Default to null -> Uses the default getter
        var aggridGetter = null;
        if (getter in this.getter && typeof this.getter[getter] == 'function') {
            var finalGetterParams = getterParams || {};
            aggridGetter = this.getter[getter](getterParams);
        } else {
            console.warn('Getter "' + getter + '" not found! Returning agGrid default function');
        }
        return aggridGetter;
    }

    public static registerGetter(name: string, func: any) {
        this.getter[name] = func;
    }
}

export function ParamsDataGetter(getterParams) {
    return function (params: ValueGetterParams) {
        return params.data;
    };
};
StingerSoftAggridValueGetter.registerGetter('ParamsDataGetter', ParamsDataGetter);

export function DisplayValueGetter(getterParams) {
    return function (params: ValueGetterParams) {
        var value = deepFind(params.data, params.column.getColId());
        return value === null ? null : value['displayValue'];
    };
};
StingerSoftAggridValueGetter.registerGetter('DisplayValueGetter', DisplayValueGetter);

export function ValueGetter(getterParams) {
    return function (params: ValueGetterParams) {
        var value = deepFind(params.data, params.column.getColId());
        return value === null ? null : value['value'];
    };
};
StingerSoftAggridValueGetter.registerGetter('ValueGetter', ValueGetter);

export function PercentageValueGetter(getterParams) {
    return function (params: ValueGetterParams) {
        var value = deepFind(params.data, params.column.getColId());
        return value === null ? null : value['value'] * 100;
    };
};
StingerSoftAggridValueGetter.registerGetter('ValueGetter', ValueGetter);