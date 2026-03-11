import {invokeRenderer} from "./StingerSoftAggridRenderer";


export class StingerSoftAggridTextFormatter {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    private static formatter: Record<string, any> = {}

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static getFormatter = function (this: typeof StingerSoftAggridTextFormatter, formatter: string, formatterParams: any = {}): any {
        //Default to null -> Uses the default formatter
        let aggridFormatter = null;
        if (formatter in this.formatter && typeof this.formatter[formatter] === 'function') {
            const finalFormatterParams = formatterParams || {};
            aggridFormatter = this.formatter[formatter](finalFormatterParams);
        } else {
            console.warn(`Textformatter "${formatter}" not found! Returning agGrid default function`);
        }
        return aggridFormatter;
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static registerFormatter(name: string, func: any): void {
        this.formatter[name] = func;
    }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function CellRendererTextFormatter(_formatterParams: any): (value: any, colDef: any) => any {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return function (value: any, colDef: any) {
        const {cellRenderer} = colDef.filterParams;
        const {cellRendererParams} = colDef.filterParams;
        const displayValue = invokeRenderer(cellRenderer, cellRendererParams, value);
        if (displayValue === null || displayValue === "") {
            return value;
        }
        return displayValue;
    };
}

StingerSoftAggridTextFormatter.registerFormatter('CellRendererTextFormatter', CellRendererTextFormatter);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function NullValueTextFormatter(formatterParams: any): (value: any, colDef: any) => any {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return function (value: any, _colDef: any) {
        if (value === null || value === "") {
            return formatterParams.nullValueLabel || '';
        }
        return value;
    };
}
StingerSoftAggridTextFormatter.registerFormatter('NullValueTextFormatter', NullValueTextFormatter);
