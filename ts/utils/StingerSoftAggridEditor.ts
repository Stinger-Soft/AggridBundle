import moment from 'moment';

import type {IAfterGuiAttachedParams, ICellEditorComp, ICellEditorParams} from "@ag-grid-community/core";
declare let jQuery: JQueryStatic;

// const language = jQuery('html').attr('lang') ?? 'en';
// if(language !== 'en') {
//
//     require(`moment/locale/${language}`);
// }
// moment.locale(language);

export class StingerSoftAggridEditor {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    private static editor: Record<string, any> = {};

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static getEditor = function (this: typeof StingerSoftAggridEditor, editor: string, editorParams: any = {}): any {
        //Default to null -> Uses the default formatter
        let aggridEditor = null;
        if (editor in this.editor && typeof this.editor[editor] === 'function') {
            const finalEditorParams = editorParams || {};
            aggridEditor = this.editor[editor](finalEditorParams);
        } else {
            console.warn(`Editor "${editor}" not found! Returning agGrid default function`);
        }
        return aggridEditor;
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static registerEditor(name: string, func: any): void {
        this.editor[name] = func;
    }
}

/**
 * will probably not work
 */
export class DatePicker implements ICellEditorComp {
    private eInput!: HTMLInputElement;

    getGui(): HTMLElement {
        return this.eInput;
    }

    init(params: ICellEditorParams): void {
        // create the cell
        this.eInput = document.createElement('input');

        if (typeof params.value !== "undefined" && params.value !== null) {
            this.eInput.value = moment(params.value.date).format(moment.localeData().longDateFormat('L'));
        }

        // https://jqueryui.com/datepicker/
        // @ts-expect-error jQuery UI datepicker plugin
        jQuery(this.eInput).datepicker({
            format: moment.localeData().longDateFormat('L').toLowerCase()
        });
    }

    afterGuiAttached(_params?: IAfterGuiAttachedParams): void {
        this.eInput.focus();
        this.eInput.select();
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    getValue(): any {
        if (this.eInput.value === "") {
            return null;
        }
        return {date: moment(this.eInput.value, 'L').toDate()};
    }

     
    isPopup(): boolean {
        return false;
    }

}

StingerSoftAggridEditor.registerEditor('DatePicker', DatePicker);
