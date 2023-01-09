declare var jQuery: JQueryStatic;

import {StingerSoftAggrid} from "./StingerSoftAggrid";
import {ICellRendererComp, ICellRendererParams, Promise} from "ag-grid-community";
import {isConstructor} from "./utils";

declare var Translator: any;


export class StingerSoftAggridRenderer {
    private static renderer = [];

    public static getRenderer(renderer: string, rendererParams: any = {}) {
        //Default to null -> Uses the default renderer
        var aggridRenderer = null;
        if (renderer in this.renderer && typeof this.renderer[renderer] == 'function') {
            aggridRenderer = this.renderer[renderer];
        } else {
            console.warn('Renderer "' + renderer + '" not found! Returning agGrid default function');
        }
        return aggridRenderer;
    }

    public static registerRenderer(name: string, func: any) {
        this.renderer[name] = func;
    }
}

export function invokeRenderer(aggridRenderer, rendererParams, value) {
    if (isConstructor(aggridRenderer)) {
        var cellRenderer = new aggridRenderer();
        var params = jQuery.extend({}, rendererParams || {}, {'value': value});
        cellRenderer.init(params);
        return jQuery(cellRenderer.getGui()).text();
    } else {
        var params = jQuery.extend({}, rendererParams || {}, {'value': value});
        return aggridRenderer(params);
    }
}


export class RawHtmlRenderer implements ICellRendererComp {

    private eGui: HTMLElement;

    getGui(): HTMLElement {
        return this.eGui;
    }

    init(params: ICellRendererParams): Promise<void> | void {
        var displayValue = StingerSoftAggrid.getDisplayValueFromParams(params);
        var template = document.createElement('template');
        displayValue = displayValue.trim(); // Never return a text node of whitespace as the result
        template.innerHTML = displayValue;
        this.eGui = template.content.firstChild as HTMLElement;
    }

    refresh(params: any): boolean {
        this.init(params);
        return true;
    }
}

StingerSoftAggridRenderer.registerRenderer('RawHtmlRenderer', RawHtmlRenderer);

export function NullValueRenderer(params) {
    var displayValue = StingerSoftAggrid.getDisplayValueFromParams(params);
    return displayValue ? displayValue : params.nullValueLabel;
}

StingerSoftAggridRenderer.registerRenderer('NullValueRenderer', NullValueRenderer);


/**
 * @return {function(*): string}
 */
export function StripHtmlRenderer(params) {
    var displayValue = StingerSoftAggrid.getDisplayValueFromParams(params);
    return displayValue ? jQuery("<div/>").html(displayValue).text() : '';
}

StingerSoftAggridRenderer.registerRenderer('StripHtmlRenderer', StripHtmlRenderer);

export function KeyValueMappingRenderer(rendererParams) {
    var val = StingerSoftAggrid.getValueFromParams(rendererParams);
    var translationDomain = rendererParams.hasOwnProperty('translation_domain') && rendererParams.translation_domain ? rendererParams.translation_domain : 'messages';
    var keyValueMapping = rendererParams.hasOwnProperty('keyValueMapping') && rendererParams.keyValueMapping ? rendererParams.keyValueMapping : {};
    if (val && keyValueMapping.hasOwnProperty(val)) {
        if (translationDomain && typeof Translator !== "undefined") {
            return Translator.trans(keyValueMapping[val], {}, translationDomain);
        }
        return keyValueMapping[val];
    }
    return val ? val : '';
}

StingerSoftAggridRenderer.registerRenderer('KeyValueMappingRenderer', KeyValueMappingRenderer);


export class YesNoRenderer implements ICellRendererComp {

    readonly TYPE_ICON_ONLY = 'icon-only';
    readonly TYPE_ICON_TOOLTIP = 'icon-with-tooltip';
    readonly TYPE_ICON_WITH_LABEL = 'icon-with-label';
    readonly TYPE_LABEL_ONLY = 'label-only';

    private noValue = false;
    private yesValue = true;

    private noIconClass = '';
    private yesIconClass = '';

    private noLabel = '';
    private yesLabel = '';

    private eGui: HTMLElement;

    getGui(): HTMLElement {
        return this.eGui;
    }

    init(params: ICellRendererParams): Promise<void> | void {
        this.eGui = document.createElement('span');
        this.noIconClass = params['no_icon'];
        this.yesIconClass = params['yes_icon'];

        this.noLabel = params['no_label'];
        this.yesLabel = params['yes_label'];
        if (params.value !== "" && params.value !== undefined && params.value !== null) {
            var value = params.value;
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

            let icon = undefined;
            if (params['display_type'] !== this.TYPE_LABEL_ONLY) {
                this.eGui.innerHTML = "<i></i>";
                icon = this.eGui.querySelector('i');
                if (value == this.noValue) {
                    icon.className = this.noIconClass;
                } else if (value == this.yesValue) {
                    icon.className = this.yesIconClass;
                }
            }
            if (params['display_type'] === this.TYPE_LABEL_ONLY || params['display_type'] === this.TYPE_ICON_WITH_LABEL) {
                if (value == this.noValue) {
                    let textnode = document.createTextNode(this.noLabel);
                    this.eGui.appendChild(textnode);
                } else if (value == this.yesValue) {
                    let textnode = document.createTextNode(this.yesLabel);
                    this.eGui.appendChild(textnode);
                }
            }
            if (params['display_type'] === this.TYPE_ICON_TOOLTIP) {
                icon.setAttribute("data-toggle", "tooltip");
                icon.setAttribute("data-container", "body");
                if (value == this.noValue) {
                    icon.setAttribute("title", this.noLabel);
                } else if (value == this.yesValue) {
                    icon.setAttribute("title", this.yesLabel);
                }
            }
        }
    }

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

    private states = {};

    private eGui: HTMLElement;

    getGui(): HTMLElement {
        return this.eGui;
    }

    init(params: ICellRendererParams): Promise<void> | void {
        this.eGui = document.createElement('span');
        this.states = params['states'];

        if (params.value !== "" && params.value !== undefined && params.value !== null) {
            var value = params.value;
            if (typeof params.value === 'object' && params.value.hasOwnProperty('value')) {
                value = params.value.value;
            }

            if (
                (params.hasOwnProperty('colDef') && params.colDef.hasOwnProperty('pivotValueColumn')) ||
                (params.hasOwnProperty('column') && params.column.hasOwnProperty('aggregationActive') && params.column.isValueActive() === true)
            ) {
                this.eGui.innerHTML = value;
                return;
            }

            if (!this.states.hasOwnProperty(value)) {
                return;
            }

            var values = Array.isArray(value) ? value : [value];
            var i = 0;

            for (var item of values) {
                if (!this.states.hasOwnProperty(item)) {
                    continue;
                }
                const stateConfig = this.states[item];

                const iconClass = stateConfig.icon;
                let label = stateConfig.label;
                const color = stateConfig.color;
                let icon = undefined;

                if (params['display_type'] !== this.TYPE_LABEL_ONLY) {
                    this.eGui.innerHTML = "<i></i>";
                    icon = document.createElement('i');
                    icon.className = iconClass + ' ' + color;
                    this.eGui.appendChild(icon);
                }
                if (params['display_type'] === this.TYPE_LABEL_ONLY || params['display_type'] === this.TYPE_ICON_WITH_LABEL) {
                    if (params['display_type'] === this.TYPE_ICON_WITH_LABEL) {
                        label = ' ' + label;
                    }
                    let textnode = document.createTextNode(label);
                    this.eGui.appendChild(textnode);
                }
                if (params['display_type'] === this.TYPE_ICON_TOOLTIP) {
                    icon.setAttribute("data-toggle", "tooltip");
                    icon.setAttribute("data-container", "body");
                    icon.setAttribute("title", label);
                }

                if (++i < values.length) {
                    let textnode = document.createTextNode(", ");
                    this.eGui.appendChild(textnode);
                }
            }
        }

    }

    refresh(params: any): boolean {
        this.init(params);
        return true;
    }
}

StingerSoftAggridRenderer.registerRenderer('StateRenderer', StateRenderer);
