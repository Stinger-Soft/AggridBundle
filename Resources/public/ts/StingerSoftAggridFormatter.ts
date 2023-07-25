declare var jQuery: JQueryStatic;

const language = jQuery('html').attr('lang') || 'en';
if(language !== 'en') {
    require('moment/locale/' + language);
}
const moment = require('moment');
moment.locale(language);

import {StingerSoftAggrid} from "./StingerSoftAggrid";
import {ICellRendererComp, ICellRendererParams, AgPromise} from "ag-grid-community";


export class StingerSoftAggridFormatter {
    private static formatter = [];

    public static getFormatter = function (formatter, formatterParams: any = {}) {
        //Default to null -> Uses the default formatter
        var aggridFormatter = null;
        if (formatter in this.formatter && typeof this.formatter [formatter] == 'function') {
            var finalFormatterParams = formatterParams || {};
            aggridFormatter = this.formatter[formatter](finalFormatterParams);
        } else {
            console.warn('Formatter "' + formatter + '" not found! Returning agGrid default function');
        }
        return aggridFormatter;
    }

    public static registerFormatter(name: string, func: any) {
        this.formatter[name] = func;
    }
}

export function DateTimeObjectFormatter(formatterParams) {
    return function (params) {
        var value = StingerSoftAggrid.getValueFromParams(params);
        if (value) {
            var date = typeof value == "object" ? value.date : value
            var format = formatterParams.hasOwnProperty('dateFormat') ? formatterParams.dateFormat : 'L LTS';
            return moment(date).format(format);
        }
        return '';
    };
};
StingerSoftAggridFormatter.registerFormatter('DateTimeObjectFormatter', DateTimeObjectFormatter);

export function NullFormatter(formatterParams) {
    return function (params) {
        return '';
    };
};
StingerSoftAggridFormatter.registerFormatter('NullFormatter', NullFormatter);

export function DisplayValueFormatter(formatterParams) {
    return function (params) {
        var displayValue = StingerSoftAggrid.getDisplayValueFromParams(params);
        return displayValue === null ? '' : displayValue;
    };
};
StingerSoftAggridFormatter.registerFormatter('DisplayValueFormatter', DisplayValueFormatter);

export function ValueFormatter(formatterParams) {
    return function (params) {
        var value = StingerSoftAggrid.getValueFromParams(params);
        return value === null ? '' : value;
    };
};
StingerSoftAggridFormatter.registerFormatter('ValueFormatter', ValueFormatter);

export function StripHtmlDisplayValueFormatter(formatterParams) {
    return function (params) {
        var displayValue = StingerSoftAggrid.getDisplayValueFromParams(params);
        return displayValue === null ? '' : jQuery(displayValue).text();
    };
};
StingerSoftAggridFormatter.registerFormatter('StripHtmlDisplayValueFormatter', StripHtmlDisplayValueFormatter);

StingerSoftAggridFormatter.registerFormatter('DefaultFormatter', DisplayValueFormatter);
