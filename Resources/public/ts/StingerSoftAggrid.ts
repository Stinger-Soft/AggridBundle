/// <reference types="jquery">
import type jQuery from 'jquery';
import {ColumnApi, Grid, GridApi, GridOptions} from "ag-grid-community";
import {GridConfiguration} from "./GridConfiguration";
import {StingerSoftAggridRenderer} from "./StingerSoftAggridRenderer";
import {StingerSoftAggridValueGetter} from "./StingerSoftAggridValueGetter";
import {StingerSoftAggridFormatter} from "./StingerSoftAggridFormatter";
import {StingerSoftAggridComparator} from "./StingerSoftAggridComparator";
import {StingerSoftAggridFilter} from "./StingerSoftAggridFilter";
import {StingerSoftAggridEditor} from "./StingerSoftAggridEditor";
import {StingerSoftAggridStyler} from "./StingerSoftAggridStyler";
import {StingerSoftAggridTextFormatter} from "./StingerSoftAggridTextFormatter";

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

    private filterTimeoutHandle: number = undefined;

    private foreignFormSelectInputId = false;

    private isServerSide: boolean = false;

    private quickFilterSearchString: string = '';

    public static Comparator = StingerSoftAggridComparator;

    public static Editor = StingerSoftAggridEditor;

    public static Filter = StingerSoftAggridFilter;

    public static Formatter = StingerSoftAggridFormatter;

    public static Getter = StingerSoftAggridValueGetter;

    public static Renderer = StingerSoftAggridRenderer;

    public static Styler = StingerSoftAggridStyler;

    public static TextFormatter = StingerSoftAggridTextFormatter;


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
        this.loadState();
    }

    private setLicenseKey(licenseKey: string) {
        this.licenseKey = licenseKey;
    }

    public static getValueFromParams = function (params) {
        if (params.value !== null && typeof params.value === 'object' && params.value.hasOwnProperty('value')) {
            return params.value.value;
        }
        return params.value;
    };

    public static getDisplayValueFromParams = function (params) {
        if (params.value !== null && typeof params.value === 'object' && params.value.hasOwnProperty('displayValue')) {
            return params.value.displayValue;
        }
        return this.getValueFromParams(params);
    };

    private fetchRowsViaAjax() {
        var that = this;
        return jQuery.getJSON(this.options.stinger.ajaxUrl, {
            'agGrid': {
                'gridId': '#' + that.gridId
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
                requestObject['gridId'] = '#' + that.gridId;
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
            console.log('fetching data');
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

    public registerjQueryListeners() {
        var that = this;

        let searchField: jQuery = undefined;
        if (this.options.stinger.searchEnabled) {
            searchField = jQuery(this.gridId + '_search');
            searchField.on('input keyup change', function () {
                var value = jQuery(this).val();
                that.quickFilter(value);
            });
        }

        let paginationDropdown: jQuery = undefined;
        if (this.options.aggrid.hasOwnProperty('pagination') && this.options.aggrid.pagination) {
            paginationDropdown = jQuery(this.gridId + '_paginationDropdown');
            paginationDropdown.on('change', function () {
                var value = jQuery(this).val();
                that.api.paginationSetPageSize(Number(value));
            });
        }

        if (this.options.stinger.hasOwnProperty('clearFilterButton') && this.options.stinger.clearFilterButton) {
            jQuery(this.gridId + '_clear').on('click', () => {
                searchField.val('');
                that.resetFilter();
            });
        }

        if (this.options.stinger.hasOwnProperty('reloadButton') && this.options.stinger.reloadButton) {
            var that = this;
            jQuery(this.gridId + '_reload').on('click', function () {
                that.reload();
            });
        }

        if (this.options.stinger.hasOwnProperty('autosizeColumnsButton') && this.options.stinger.autosizeColumnsButton) {
            jQuery(this.gridId + '_autosize').on('click', function () {
                that.autoSizeColumns();
            });
        }

        //Save to local storage
        jQuery(this.aggridElement).on("remove", function () {
            that.saveState();
        });
        window.addEventListener("beforeunload", function () {
            that.saveState();
        });
        //Refresh
        jQuery(document).on('refresh.aggrid', function () {
            that.refresh(true);
        });

        if (this.foreignFormSelectInputId !== null && this.foreignFormSelectInputId) {
            this.api.addEventListener('selectionChanged', function (event) {
                jQuery.proxy(StingerSoftAggrid.prototype.onRowSelected, that, event)();
            });
        }
    }

    public reload() {
        if (this.options.stinger.hasOwnProperty('dataMode') && this.options.stinger.dataMode === 'ajax') {
            this.api.showLoadingOverlay();
            this.fetchRowsViaAjax().always(() => {
                this.api.hideOverlay();
            });
        }
        if (this.options.stinger.hasOwnProperty('dataMode') && this.options.stinger.dataMode === 'enterprise') {
            this.api.purgeServerSideCache();
        }
        this.refresh(true);
    }

    public refresh(force: boolean = false) {
        this.api.refreshCells({
            "force": force
        });
    }

    public autoSizeColumns() {
        var that = this;
        var interval = setInterval(function () {
            if (that.checkIfBlocksLoaded()) {
                clearInterval(interval);
                that.autoSizeColumns();
            }
        }, 50);
    }

    /**
     * Adds all selected ids (if any) to a given foreign select form input field (if any)
     *
     * @param event
     */
    private onRowSelected() {
        if (this.foreignFormSelectInputId) {
            var $field = jQuery('#' + this.foreignFormSelectInputId);
            if ($field.length > 0) {
                $field.val(Object.values(this.getSelectedIds()).join(','));
                $field.change();
            }
        }
    }

    public getSelectedIds(field?: string) {
        var _field = field || "id";
        var selectedRows = this.api.getSelectedRows();
        var selectedIds = [];
        selectedRows.forEach(function (selectedRow, index) {
            if (_field in selectedRow) {
                selectedIds.push(selectedRow[_field].value);
            }
        });
        return selectedIds;
    }

    private checkIfBlocksLoaded = function () {
        if (this.api.getCacheBlockState() === null) {
            return false;
        }

        var status = this.api.getCacheBlockState()[0]
            ? this.api.getCacheBlockState()[0].pageStatus
            : false;
        return status === 'loaded';
    };

    public resetFilter() {
        if (this.isServerSide) {
            this.quickFilterSearchString = '';
        } else {
            this.api.setQuickFilter('');
        }
        this.api.setFilterModel(null);
        this.api.onFilterChanged();
    }

    public saveState() {
        if (window.localStorage && this.options.stinger.persistState) {
            var storage = window.localStorage;

            var storageKey = this.stateSavePrefix + this.stateSaveKey;
            var storageObject = {
                columns: this.columnApi.getColumnState(),
                groups: this.columnApi.getColumnGroupState(),
                sorts: this.api.getSortModel(),
                filters: this.api.getFilterModel(),
                version: this.options.stinger.versionHash
            };
            storage.setItem(storageKey, JSON.stringify(storageObject));
        }
    }

    public loadState() {
        if (window.localStorage && this.options.stinger.persistState) {
            var storage = window.localStorage;

            var storageKey = this.stateSavePrefix + this.stateSaveKey;
            var storageObject = JSON.parse(storage.getItem(storageKey));
            if (storageObject !== null && typeof storageObject === 'object' && storageObject.hasOwnProperty('version')) {
                if (storageObject.version === this.options.stinger.versionHash) {
                    var columnState = storageObject.hasOwnProperty('columns') && storageObject.columns ? storageObject.columns : [];
                    var columnGroupState = storageObject.hasOwnProperty('groups') && storageObject.groups ? storageObject.groups : [];
                    var sortModel = storageObject.hasOwnProperty('sorts') && storageObject.sorts ? storageObject.sorts : [];
                    var filterModel = storageObject.hasOwnProperty('filters') && storageObject.filters ? storageObject.filters : {};
                    if (columnState && Array.isArray(columnState) && columnState.length) {
                        this.columnApi.setColumnState(columnState);
                    }
                    if (columnGroupState && Array.isArray(columnGroupState) && columnGroupState.length) {
                        this.columnApi.setColumnGroupState(columnGroupState);
                    }
                    if (sortModel && Array.isArray(sortModel) && sortModel.length) {
                        this.api.setSortModel(sortModel);
                    }
                    if (filterModel && Object.keys(filterModel).length !== 0) {
                        this.api.setFilterModel(filterModel);
                    }
                }
            }
        }
    }

    public quickFilter(searchString: string) {
        if (!this.options.stinger.searchEnabled) {
            console.warn('search is not enabled!');
        }
        var that = this;
        if (this.filterTimeoutHandle) {
            clearTimeout(this.filterTimeoutHandle);
        }
        this.filterTimeoutHandle = setTimeout(() => {
            if (searchString === that.quickFilterSearchString) {
                return;
            }
            that.quickFilterSearchString = searchString;
            if (that.isServerSide) {
                that.api.onFilterChanged();
            } else {
                that.api.setQuickFilter(searchString);
            }
        }, this.filterTimeout);
    }

}