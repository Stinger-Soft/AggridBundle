import {StingerSoftAggrid} from "./StingerSoftAggrid";
import {ICellRendererComp, ICellRendererParams} from "@ag-grid-community/core";
import {invokeRenderer, StingerSoftAggridRenderer} from "./StingerSoftAggridRenderer";


export class StingerSoftAggridTextFormatter {
    private static formatter = [];

    public static getFormatter = function (formatter, formatterParams: any = {}) {
        //Default to null -> Uses the default formatter
        var aggridFormatter = null;
        if (formatter in this.formatter && typeof this.formatter [formatter] == 'function') {
            var finalFormatterParams = formatterParams || {};
            aggridFormatter = this.formatter[formatter](finalFormatterParams);
        } else {
            console.warn('Textformatter "' + formatter + '" not found! Returning agGrid default function');
        }
        return aggridFormatter;
    }

    public static registerFormatter(name: string, func: any) {
        this.formatter[name] = func;
    }
}

export function CellRendererTextFormatter(formatterParams) {
    return function (value, colDef) {
        var cellRenderer = colDef.filterParams.cellRenderer;
        var cellRendererParams = colDef.filterParams.cellRendererParams;
        var displayValue = invokeRenderer(cellRenderer, cellRendererParams, value);
        if (displayValue === null || displayValue === "") {
            return value;
        }
        return displayValue;
    };
}

StingerSoftAggridTextFormatter.registerFormatter('CellRendererTextFormatter', CellRendererTextFormatter);

export function NullValueTextFormatter(formatterParams) {
    return function (value, colDef) {
        if (value === null || value === "") {
            return formatterParams.nullValueLabel || '';
        }
        return value;
    };
};
StingerSoftAggridTextFormatter.registerFormatter('NullValueTextFormatter', NullValueTextFormatter);