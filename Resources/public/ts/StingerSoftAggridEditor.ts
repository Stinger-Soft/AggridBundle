declare var jQuery: JQueryStatic;

const language = jQuery('html').attr('lang') || 'en';
if(language !== 'en') {
    require('moment/locale/' + language);
}
const moment = require('moment');
moment.locale(language);

import {IAfterGuiAttachedParams, ICellEditorComp, ICellEditorParams, AgPromise} from "@ag-grid-community/core";

export class StingerSoftAggridEditor {
    private static editor = [];

    public static getEditor = function (editor: string, editorParams: any = {}) {
        //Default to null -> Uses the default formatter
        var aggridEditor = null;
        if (editor in this.editor && typeof this.editor [editor] == 'function') {
            var finalEditorParams = editorParams || {};
            aggridEditor = this.editor[editor](finalEditorParams);
        } else {
            console.warn('Editor "' + editor + '" not found! Returning agGrid default function');
        }
        return aggridEditor;
    }

    public static registerEditor(name: string, func: any) {
        this.editor[name] = func;
    }
}

/**
 * will probably not work
 */
export class DatePicker implements ICellEditorComp {
    private eInput: HTMLInputElement;

    getGui(): HTMLElement {
        return this.eInput;
    }

    init(params: ICellEditorParams): AgPromise<void> | void {
        // create the cell
        this.eInput = document.createElement('input');

        if (typeof params.value !== "undefined" && params.value !== null) {
            this.eInput.value = moment(params.value.date).format(moment.localeData().longDateFormat('L'));
        }

        // https://jqueryui.com/datepicker/
        // @ts-ignore
        jQuery(this.eInput).datepicker({
            format: moment.localeData().longDateFormat('L').toLowerCase()
        });
    }

    afterGuiAttached(params?: IAfterGuiAttachedParams) {
        this.eInput.focus();
        this.eInput.select();
    }

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