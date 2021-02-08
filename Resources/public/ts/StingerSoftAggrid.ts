/// <reference types="jquery">
import type jQuery from 'jquery';
import {ColumnApi, Grid, GridApi, GridOptions} from "ag-grid-community";
import {GridConfiguration} from "./GridConfiguration";

declare var jQuery: jQuery;

export class StingerSoftAggrid {

    private gridId?: string;

    private licenseKey?: string;

    private options: GridConfiguration;

    private stateSavePrefix: string = "StingerSoftAggrid_";

    private stateSaveKey: string;

    private resizedColumns: any[] = [];

    private clipboardValueFormatters: any = {};

    private filterTimeout: number = 500;

    private foreignFormSelectInputId = false;

    private isServerSide: boolean = false;

    private quickFilterSearchString: string = '';

    constructor(private aggridElement: HTMLElement, private api?: GridApi, private columnApi?: ColumnApi) {
        this.gridId = aggridElement.id;
        this.stateSaveKey = this.gridId.replace("#", "");
    }

    public init(options: GridConfiguration): void {
        this.options = options;
        if (this.options.hasOwnProperty('enterpriseLicense')) {
            this.setLicenseKey(this.options.stinger.enterpriseLicense);
        }

        if (!this.api) {
            new Grid(this.aggridElement, this.options.aggrid);
            this.api = this.options.aggrid.api;
            this.columnApi = this.options.aggrid.columnApi;
        }

        //Init
        this.handleOptions();
        // this.registerListeners();
        // this.load();
    }

    private setLicenseKey(licenseKey: string) {
        this.licenseKey = licenseKey;
    }

    private getValueFromParams = function (params) {
        if (params.value !== null && typeof params.value === 'object' && params.value.hasOwnProperty('value')) {
            return params.value.value;
        }
        return params.value;
    };

    private getDisplayValueFromParams = function (params) {
        if (params.value !== null && typeof params.value === 'object' && params.value.hasOwnProperty('displayValue')) {
            return params.value.displayValue;
        }
        return this.getValueFromParams(params);
    };

    private fetchRowsViaAjax() {
        var that = this;
        jQuery.getJSON(this.options.stinger.ajaxUrl, {
            'agGrid': {
                'gridId': that.gridId
            }
        }, function (data) {
            that.api.setRowData(data.items);
        });
    }

    private getEnterpriseDatasource() {
        var that = this;
        return {
            url: this.options.stinger.ajaxUrl,
            ajaxReq: null,
            getRows: function (params) {
                var searchString = that.quickFilterSearchString || '';
                var requestObject = params.request;
                requestObject['search'] = searchString;
                requestObject['gridId'] = that.gridId;
                that.api.showLoadingOverlay();
                this.ajaxReq = jQuery.post(this.url, {
                    'agGrid': requestObject,
                }, function (data) {
                    params.successCallback(data.items, data.total);
                    that.api.hideOverlay();
                }, "json").fail(function () {
                    that.api.hideOverlay();
                    params.failCallback();
                });
            }
        }
    }

    private handleOptions() {
        this.isServerSide = false;
        var that = this;
        if (this.options.stinger.dataMode === 'ajax') {
            this.fetchRowsViaAjax();
        }
        if (this.options.hasOwnProperty('dataMode') && this.options.stinger.dataMode === 'enterprise') {
            this.isServerSide = true;
            this.api.setServerSideDatasource(this.getEnterpriseDatasource());
        }
        if (this.options.stinger.defaultOrderProperties) {
            var orderColumns = this.options.stinger.defaultOrderProperties || [];
            var keys = Object.keys(orderColumns);
            for (let path in orderColumns) {
                var column = that.columnApi.getColumn(path);
                if (column !== null) {
                    column.setSort(orderColumns[path] || 'asc');
                }
            }
        } else if (this.options.hasOwnProperty('defaultOrderProperty')) {
            var column = this.columnApi.getColumn(this.options.stinger.defaultOrderProperty);
            if (column !== null) {
                column.setSort(this.options.stinger.defaultOrderDirection ? this.options.stinger.defaultOrderDirection : 'asc');
            }
        }
    }

}