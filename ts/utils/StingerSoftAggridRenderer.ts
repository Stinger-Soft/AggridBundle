import type {BazingaTranslator} from "bazinga-translator";

import {StingerSoftAggrid} from "./StingerSoftAggrid";
import type {ICellRendererComp, ICellRendererParams, AgPromise} from "@ag-grid-community/core";
import {isConstructor} from "./utils";

declare let jQuery: JQueryStatic;

declare let Translator: BazingaTranslator;

export class StingerSoftAggridRenderer {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    private static renderer: Record<string, any> = {}

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static getRenderer(renderer: string, rendererParams: any = {}): any {
        //Default to null -> Uses the default renderer
        let aggridRenderer = null;
        if (renderer in this.renderer && typeof this.renderer[renderer] === 'function') {
            aggridRenderer = this.renderer[renderer];
        } else {
            console.warn(`Renderer "${renderer}" not found! Returning agGrid default function`);
        }
        return aggridRenderer;
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static registerRenderer(name: string, func: any): void {
        this.renderer[name] = func;
    }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function invokeRenderer(aggridRenderer: any, rendererParams: any, value: any): any {
    const params = jQuery.extend({}, rendererParams || {}, {value});
    if (isConstructor(aggridRenderer)) {
         
        const cellRenderer: ICellRendererComp = new aggridRenderer() as ICellRendererComp;
        cellRenderer.init!(params);
        return jQuery(cellRenderer.getGui()).text();
    } else {
        return aggridRenderer(params);
    }
}


export class RawHtmlRenderer implements ICellRendererComp {

    private eGui!: HTMLElement;

    getGui(): HTMLElement {
        return this.eGui;
    }

    init(params: ICellRendererParams): void {
        let displayValue = StingerSoftAggrid.getDisplayValueFromParams(params);
        const template = document.createElement('template');
        displayValue = displayValue.trim(); // Never return a text node of whitespace as the result
        template.innerHTML = displayValue;
        this.eGui = template.content.firstChild as HTMLElement;
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    refresh(params: any): boolean {
        this.init(params);
        return true;
    }
}

StingerSoftAggridRenderer.registerRenderer('RawHtmlRenderer', RawHtmlRenderer);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function NullValueRenderer(params: any): any {
    const displayValue = StingerSoftAggrid.getDisplayValueFromParams(params);
    return displayValue || params.nullValueLabel;
}

StingerSoftAggridRenderer.registerRenderer('NullValueRenderer', NullValueRenderer);


/**
 * @return {function(*): string}
 */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function StripHtmlRenderer(params: any): string {
    const displayValue = StingerSoftAggrid.getDisplayValueFromParams(params);
    return displayValue ? jQuery("<div/>").html(displayValue).text() : '';
}

StingerSoftAggridRenderer.registerRenderer('StripHtmlRenderer', StripHtmlRenderer);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function KeyValueMappingRenderer(rendererParams: any): any {
    const val = StingerSoftAggrid.getValueFromParams(rendererParams);
    const translationDomain = rendererParams.hasOwnProperty('translation_domain') && rendererParams.translation_domain ? rendererParams.translation_domain : 'messages';
    const keyValueMapping = rendererParams.hasOwnProperty('keyValueMapping') && rendererParams.keyValueMapping ? rendererParams.keyValueMapping : {};
    if (val && keyValueMapping.hasOwnProperty(val)) {
        if (translationDomain && typeof Translator !== "undefined") {
            return Translator.trans(keyValueMapping[val], {}, translationDomain);
        }
        return keyValueMapping[val];
    }
    return val || '';
}

StingerSoftAggridRenderer.registerRenderer('KeyValueMappingRenderer', KeyValueMappingRenderer);


export class YesNoRenderer implements ICellRendererComp {

    readonly TYPE_ICON_ONLY = 'icon-only';
    readonly TYPE_ICON_TOOLTIP = 'icon-with-tooltip';
    readonly TYPE_ICON_WITH_LABEL = 'icon-with-label';
    readonly TYPE_LABEL_ONLY = 'label-only';

    private readonly noValue = false;
    private readonly yesValue = true;

    private noIconClass = '';
    private yesIconClass = '';

    private noLabel = '';
    private yesLabel = '';

    private eGui!: HTMLElement;

    getGui(): HTMLElement {
        return this.eGui;
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    init(params: any): AgPromise<void> | void {
        this.eGui = document.createElement('span');
        this.noIconClass = params.no_icon;
        this.yesIconClass = params.yes_icon;

        this.noLabel = params.no_label;
        this.yesLabel = params.yes_label;
        if (params.value !== "" && params.value !== undefined && params.value !== null) {
            let {value} = params;
            if (typeof params.value === 'object' && params.value.hasOwnProperty('displayValue')) {
                value = params.value.displayValue;
            }
            value = value === 'true' ? true : value;
            value = value === 'false' ? false : value;

            if (
                (params.hasOwnProperty('colDef') && params.colDef.hasOwnProperty('pivotValueColumn')) ||
                (params.hasOwnProperty('column') && params.column.hasOwnProperty('aggregationActive') && params.column.isValueActive())
            ) {
                this.eGui.innerHTML = value;
                return;
            }

            let icon: HTMLElement | null | undefined;
            if (params.display_type !== this.TYPE_LABEL_ONLY) {
                this.eGui.innerHTML = "<i></i>";
                icon = this.eGui.querySelector('i');
                if (value === this.noValue) {
                    icon!.className = this.noIconClass;
                } else if (value === this.yesValue) {
                    icon!.className = this.yesIconClass;
                }
            }
            if (params.display_type === this.TYPE_LABEL_ONLY || params.display_type === this.TYPE_ICON_WITH_LABEL) {
                if (value === this.noValue) {
                    const textnode = document.createTextNode(this.noLabel);
                    this.eGui.appendChild(textnode);
                } else if (value === this.yesValue) {
                    const textnode = document.createTextNode(this.yesLabel);
                    this.eGui.appendChild(textnode);
                }
            }
            if (params.display_type === this.TYPE_ICON_TOOLTIP) {
                icon!.setAttribute("data-toggle", "tooltip");
                icon!.setAttribute("data-container", "body");
                if (value === this.noValue) {
                    icon!.setAttribute("title", this.noLabel);
                } else if (value === this.yesValue) {
                    icon!.setAttribute("title", this.yesLabel);
                }
            }
        }
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    refresh(params: any): boolean {
        this.init(params);
        return true;
    }
}

StingerSoftAggridRenderer.registerRenderer('YesNoRenderer', YesNoRenderer);

export class StateRenderer implements ICellRendererComp {

    //"Constants"
    readonly TYPE_ICON_ONLY = 'icon-only';
    readonly TYPE_ICON_TOOLTIP = 'icon-with-tooltip';
    readonly TYPE_ICON_WITH_LABEL = 'icon-with-label';
    readonly TYPE_LABEL_ONLY = 'label-only';

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    private states: Record<string, any> = {};

    private eGui!: HTMLElement;

    getGui(): HTMLElement {
        return this.eGui;
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    init(params: any): AgPromise<void> | void {
        this.eGui = document.createElement('span');
        this.states = Object.hasOwn(params, "states") ? params.states : [];

        if (params.value !== "" && params.value !== undefined && params.value !== null) {
            let {value} = params;
            if (typeof params.value === 'object' && params.value.hasOwnProperty('value')) {
                value = params.value.value;
            }

            if (
                (params.hasOwnProperty('colDef') && params.colDef.hasOwnProperty('pivotValueColumn')) ||
                (params.hasOwnProperty('column') && params.column.hasOwnProperty('aggregationActive') && params.column.isValueActive())
            ) {
                this.eGui.innerHTML = value;
                return;
            }

            if (!this.states.hasOwnProperty(value)) {
                return;
            }

            const values: string[] = Array.isArray(value) ? value : [value];
            let i = 0;

            for (const item of values) {
                if (!this.states.hasOwnProperty(item)) {
                    continue;
                }
                const stateConfig = this.states[item];

                const iconClass = stateConfig.icon;
                let {label} = stateConfig;
                const {color} = stateConfig;
                let icon: HTMLElement | undefined;

                if (params.display_type !== this.TYPE_LABEL_ONLY) {
                    this.eGui.innerHTML = "<i></i>";
                    icon = document.createElement('i');
                    icon.className = `${iconClass} ${color}`;
                    this.eGui.appendChild(icon);
                }
                if (params.display_type === this.TYPE_LABEL_ONLY || params.display_type === this.TYPE_ICON_WITH_LABEL) {
                    if (params.display_type === this.TYPE_ICON_WITH_LABEL) {
                        label = ` ${label}`;
                    }
                    const textnode = document.createTextNode(label);
                    this.eGui.appendChild(textnode);
                }
                if (params.display_type === this.TYPE_ICON_TOOLTIP) {
                    icon!.setAttribute("data-toggle", "tooltip");
                    icon!.setAttribute("data-container", "body");
                    icon!.setAttribute("title", label);
                }

                i += 1;
                if (i < values.length) {
                    const textnode = document.createTextNode(", ");
                    this.eGui.appendChild(textnode);
                }
            }
        }

    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    refresh(params: any): boolean {
        this.init(params);
        return true;
    }
}

StingerSoftAggridRenderer.registerRenderer('StateRenderer', StateRenderer);
