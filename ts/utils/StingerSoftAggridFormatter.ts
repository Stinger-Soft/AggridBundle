import moment from 'moment';

import {StingerSoftAggrid} from "./StingerSoftAggrid";
declare let jQuery: JQueryStatic;
//
// const language = jQuery('html').attr('lang') ?? 'en';
// if(language !== 'en') {
//
//     require(`moment/locale/${language}`);
// }
// moment.locale(language);


export class StingerSoftAggridFormatter {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    private static formatter: Record<string, any> = {};

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static getFormatter = function (this: typeof StingerSoftAggridFormatter, formatter: string, formatterParams: any = {}): any {
        //Default to null -> Uses the default formatter
        let aggridFormatter = null;
        if (formatter in this.formatter && typeof this.formatter [formatter] === 'function') {
            const finalFormatterParams = formatterParams || {};
            aggridFormatter = this.formatter[formatter](finalFormatterParams);
        } else {
            console.warn(`Formatter "${formatter}" not found! Returning agGrid default function`);
        }
        return aggridFormatter;
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static registerFormatter(name: string, func: any): void {
        this.formatter[name] = func;
    }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function DateTimeObjectFormatter(_formatterParams: any): (params: any) => string {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return function (params: any) {
        const value = StingerSoftAggrid.getValueFromParams(params);
        if (value) {
            const date = typeof value === "object" ? value.date : value;
            const format = _formatterParams.hasOwnProperty('dateFormat') ? _formatterParams.dateFormat : 'L LTS';
            return moment(date).format(format);
        }
        return '';
    };
}
StingerSoftAggridFormatter.registerFormatter('DateTimeObjectFormatter', DateTimeObjectFormatter);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function NullFormatter(_formatterParams: any): (_params: any) => string {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return function (_params: any) {
        return '';
    };
}
StingerSoftAggridFormatter.registerFormatter('NullFormatter', NullFormatter);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function DisplayValueFormatter(_formatterParams: any): (params: any) => any {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return function (params: any) {
        const displayValue = StingerSoftAggrid.getDisplayValueFromParams(params);
        return displayValue === null ? '' : displayValue;
    };
}
StingerSoftAggridFormatter.registerFormatter('DisplayValueFormatter', DisplayValueFormatter);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function ValueFormatter(_formatterParams: any): (params: any) => any {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return function (params: any) {
        const value = StingerSoftAggrid.getValueFromParams(params);
        return value === null ? '' : value;
    };
}
StingerSoftAggridFormatter.registerFormatter('ValueFormatter', ValueFormatter);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function StripHtmlDisplayValueFormatter(_formatterParams: any): (params: any) => string {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return function (params: any) {
        const displayValue = StingerSoftAggrid.getDisplayValueFromParams(params);
        return displayValue === null ? '' : jQuery(displayValue).text();
    };
}
StingerSoftAggridFormatter.registerFormatter('StripHtmlDisplayValueFormatter', StripHtmlDisplayValueFormatter);

StingerSoftAggridFormatter.registerFormatter('DefaultFormatter', DisplayValueFormatter);
