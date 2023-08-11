declare var jQuery: JQueryStatic;

import {ColDef, ColGroupDef, Column, ColumnApi, ColumnResizedEvent, Grid, GridApi, GridOptions} from "ag-grid-community";
import {GridConfiguration} from "./GridConfiguration";
import {StingerSoftAggridRenderer} from "./StingerSoftAggridRenderer";
import {StingerSoftAggridValueGetter} from "./StingerSoftAggridValueGetter";
import {StingerSoftAggridFormatter} from "./StingerSoftAggridFormatter";
import {StingerSoftAggridComparator} from "./StingerSoftAggridComparator";
import {StingerSoftAggridFilter} from "./StingerSoftAggridFilter";
import {StingerSoftAggridEditor} from "./StingerSoftAggridEditor";
import {StingerSoftAggridStyler} from "./StingerSoftAggridStyler";
import {StingerSoftAggridTextFormatter} from "./StingerSoftAggridTextFormatter";
import {StingerSoftAggridKeyCreator} from "./StingerSoftAggridKeyCreator";
import {StingerSoftAggridTooltip} from "./StingerSoftAggridTooltip";
import type { BazingaTranslator } from 'bazinga-translator';

declare var Translator: BazingaTranslator;

export class StingerSoftAggrid {

    private gridId?: string;

    private licenseKey?: string;

    private options: GridConfiguration;

    private stateSavePrefix: string = "StingerSoftAggrid_";

    private stateSaveKey: string;

    private resizedColumns: Column[] = [];

    private clipboardValueFormatters: any = {};

    private filterTimeout: number = 500;

    private filterTimeoutHandle: any = undefined;

    private foreignFormSelectInputId = false;

    private isServerSide: boolean = false;

    private quickFilterSearchString: string = '';

    public static Comparator = StingerSoftAggridComparator;

    public static Creator = StingerSoftAggridKeyCreator;

    public static Editor = StingerSoftAggridEditor;

    public static Filter = StingerSoftAggridFilter;

    public static Formatter = StingerSoftAggridFormatter;

    public static Getter = StingerSoftAggridValueGetter;

    public static Renderer = StingerSoftAggridRenderer;

    public static Styler = StingerSoftAggridStyler;

    public static TextFormatter = StingerSoftAggridTextFormatter;

    public static Tooltip = StingerSoftAggridTooltip;

    constructor(private aggridElement: HTMLElement, public api?: GridApi, public columnApi?: ColumnApi) {
        this.gridId = aggridElement.id;
        this.stateSaveKey = this.gridId.replace("#", "");
    }

    public init(options: GridConfiguration): void {
        this.options = options;


        if (!this.api) {
            if (this.options.stinger.hasOwnProperty('enterpriseLicense')) {
                this.setLicenseKey(this.options.stinger.enterpriseLicense);
            }
            var aggrid = new Grid(this.aggridElement, this.options.aggrid);
            this.api = this.options.aggrid.api;
            this.columnApi = this.options.aggrid.columnApi;
        }

        //Init
        this.handleOptions();
        this.registerDefaultListeners();
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
            },
            'gridHelper': this.options['gridHelper']
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
            this.fetchRowsViaAjax();
        }
        if (this.options.stinger.dataMode === 'enterprise') {
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

    private registerDefaultListeners() {
        let that = this;
        this.api.addEventListener('columnResized', function (event: ColumnResizedEvent) {
            if (event.source == 'uiColumnDragged') {
                for (let column of event.columns) {
                    that.addResizedColumn(column);
                }
            }
        });
    }

    public registerjQueryListeners() {
        var that = this;

        let searchField: JQuery = undefined;
        if (this.options.stinger.searchEnabled) {
            searchField = jQuery(this.gridId + '_search');
            searchField.on('input keyup change', function () {
                var value = jQuery(this).val();
                that.quickFilter(String(value));
            });
        }

        let paginationDropdown: JQuery = undefined;
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

    private addResizedColumn(column: Column) {
        if (this.resizedColumns.indexOf(column) === -1) {
            this.resizedColumns.push(column);
        }
    }

    public setPaginationPageSize(entriesPerPage) {
        this.api.paginationSetPageSize(Number(entriesPerPage));
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

    public autoSizeColumnsWhenReady() {
        var that = this;
        var interval = setInterval(function () {
            if (that.checkIfBlocksLoaded()) {
                clearInterval(interval);
                that.autoSizeColumns();
            }
        }, 50);
    }

    public autoSizeColumns(resizeWithWidthSpecified?: boolean, resizeManuallyResized?: boolean) {
        resizeWithWidthSpecified = typeof resizeWithWidthSpecified === null ? this.options.stinger.autoResizeFixedWidthColumns : resizeWithWidthSpecified;
        resizeManuallyResized = typeof resizeManuallyResized === null ? this.options.stinger.autoResizeManuallyResizedColumns : resizeManuallyResized;

        var that = this;
        var columnIdsToResize = [];
        this.columnApi.getAllColumns().forEach((column: Column) => {
            var columnWasManuallyResized = that.resizedColumns.indexOf(column) !== -1;
            if (columnWasManuallyResized && !resizeManuallyResized) {
                return;
            }
            var columnHasWidthSpecified = "width" in column.getColDef();
            if (columnHasWidthSpecified && !resizeWithWidthSpecified) {
                this.columnApi.setColumnWidth(column, column.getColDef().width);
            } else {
                columnIdsToResize.push(column.getColId());
            }
        });
        if (columnIdsToResize.length > 0) {
            this.columnApi.autoSizeColumns(columnIdsToResize);
        }
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
    }

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

    protected static processJsonColumnConfiguration(column: (ColDef | ColGroupDef), configuration: GridConfiguration): (ColDef | ColGroupDef) {
        if (column.hasOwnProperty('render_html') && column['render_html']) {
            column['cellRenderer'] = (params) => {
                return params.value ? params.value : '';
            }
        }

        if (column.hasOwnProperty('cellRenderer') && column['cellRenderer']) {
            column['cellRenderer'] = StingerSoftAggrid.Renderer.getRenderer(column['cellRenderer'], column['cellRendererParams'] || {});
        }
        if (column.hasOwnProperty('valueGetter') && column['valueGetter']) {
            column['valueGetter'] = StingerSoftAggrid.Getter.getGetter(column['valueGetter'], column['valueGetterParams'] || {});
        }
        if (column.hasOwnProperty('filterValueGetter') && column['filterValueGetter']) {
            column['filterValueGetter'] = StingerSoftAggrid.Getter.getGetter(column['filterValueGetter'], column['filterValueGetterParams'] || {});
        }

        if (column.hasOwnProperty('valueSetter') && column['valueSetter']) {

        }
        if (column.hasOwnProperty('valueFormatter') && column['valueFormatter']) {
            column['valueFormatter'] = StingerSoftAggrid.Formatter.getFormatter(column['valueFormatter'], column['valueFormatterParams'] || {});
        }

        if (column.hasOwnProperty('comparator') && column['comparator']) {
            column['comparator'] = StingerSoftAggrid.Comparator.getComparator(column['comparator']);
        }
        if (column.hasOwnProperty('getQuickFilterText') && column['getQuickFilterText']) {
            column['getQuickFilterText'] = StingerSoftAggrid.Filter.getFilter(column['getQuickFilterText'], configuration.stinger.dataMode);
        } else if (column.hasOwnProperty('valueGetter') && column['valueGetter']) {
            column['getQuickFilterText'] = column['valueGetter'];
        } else if (column.hasOwnProperty('valueFormatter') && column['valueFormatter']) {
            column['getQuickFilterText'] = column['valueFormatter'];
        } else {
            column['getQuickFilterText'] = StingerSoftAggrid.Formatter.getFormatter('DefaultFormatter');
        }

        if (column.hasOwnProperty('keyCreator') && column['keyCreator']) {
            column['keyCreator'] = StingerSoftAggrid.Creator.getKeyCreator(column['keyCreator']);
        }
        if (column.hasOwnProperty('tooltip') && column['tooltip']) {
            column['tooltip'] = StingerSoftAggrid.Tooltip.getTooltip(column['tooltip']);
        }
        if (column.hasOwnProperty('checkboxSelection') && column['checkboxSelection'] !== true && column['checkboxSelection'] !== false) {
            console.warn('Passing a callable via JSON configuration for the field [checkboxSelection] is not supported!');
            delete column['checkboxSelection'];
        }

        // if(column.hasOwnProperty('comparator') && column['comparator']) {
        //     column['comparator'] = StingerSoftAggrid.Comparator.getComparator(column['comparator']);
        // }

        if (column.hasOwnProperty('valueFormatterParams')) {
            delete column['valueFormatterParams'];
        }

        if (column.hasOwnProperty('filterParams')) {
            if (column['filterParams'] && column['filterParams'].hasOwnProperty('cellRenderer')) {
                column['filterParams'].cellRenderer = StingerSoftAggrid.Renderer.getRenderer(column['filterParams'].cellRenderer, column['filterParams'].cellRendererParams || {});
            }
            if (column['filterParams'] && column['filterParams'].hasOwnProperty('textFormatter')) {
                column['filterParams'].textFormatter = function (value) {
                    var formatter = StingerSoftAggrid.TextFormatter.getFormatter(column['filterParams'].textFormatter, column['filterParams'].textFormatterParams || {});
                    return formatter(value, this.colDef);
                }
            }
        }
        if (column.hasOwnProperty('children')) {
            for(const childColumn of (column as ColGroupDef).children) {
                StingerSoftAggrid.processJsonColumnConfiguration(childColumn as  (ColDef | ColGroupDef), configuration);
            }
        }
        return column;
    }

    public static processJsonConfiguration(configuration: GridConfiguration): void {
        for(const columnId in configuration.aggrid.columnDefs) {
            const column = configuration.aggrid.columnDefs[columnId];
            // @ts-ignore
            StingerSoftAggrid.processJsonColumnConfiguration(column, configuration);
        }
        configuration.aggrid.localeTextFunc = function (key, defaultValue) {
            var gridKey = 'stingersoft_aggrid.' + key;
            var value = Translator.trans(gridKey, {}, 'StingerSoftAggridBundle');
            if (value === gridKey) {
                console.warn('falling back to default value "' + defaultValue + '", as no translation was found for "' + key + '" (tried "' + gridKey + '" within the domain "StingerSoftAggridBundle"!');
                return defaultValue;
            }
            return value;
        }

        console.log(configuration);
    }

}