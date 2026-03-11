import {LicenseManager} from "@ag-grid-enterprise/core";

import {
    type ColGroupDef,
    type Column,
    type ColumnResizedEvent,
    createGrid,
    type GridApi,
    type GetLocaleTextParams,
    type ProcessCellForExportParams,
    type ExcelExportParams,
    type ColumnState
} from "@ag-grid-community/core";
import type {GridConfiguration} from "./GridConfiguration";
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
import type {BazingaTranslator} from 'bazinga-translator';

declare let jQuery: JQueryStatic;

declare let Translator: BazingaTranslator;

 
export class StingerSoftAggrid {

    private readonly gridId?: string;

    private licenseKey?: string;

    private options!: GridConfiguration;

    private readonly stateSavePrefix = "StingerSoftAggrid_";

    private readonly stateSaveKey: string;

    private readonly resizedColumns: Column[] = [];

    private exportableColumns: Record<string, {exportValueFormatter?: string}> = {};

    private clipboardValueFormatters:  Record<string, string> = {};

    private readonly filterTimeout = 500;

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    private filterTimeoutHandle: any = undefined;

    private readonly foreignFormSelectInputId: boolean | string = false;

    private isServerSide = false;

    private quickFilterSearchString = '';

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

    constructor(private readonly aggridElement: HTMLElement, public api?: GridApi) {
        this.gridId = aggridElement.id;
        this.stateSaveKey = this.gridId.replace("#", "");
    }

    public init(options: GridConfiguration): void {
        this.options = options;


        if (!this.api) {
            if (this.options.stinger.hasOwnProperty('enterpriseLicense')) {
                this.setLicenseKey(this.options.stinger.enterpriseLicense);
            }
            this.api = createGrid(this.aggridElement, this.options.aggrid);
        }

        //Init
        this.handleOptions();
        this.registerDefaultListeners();
        this.loadState();
    }

    private setLicenseKey(licenseKey: string): void {
        this.licenseKey = licenseKey;
        if(licenseKey) {
            LicenseManager.setLicenseKey(this.licenseKey);
        }
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static getValueFromParams = function (params: any): any {
        if (params.value !== null && typeof params.value === 'object' && params.value.hasOwnProperty('value')) {
            return params.value.value;
        }
        return params.value;
    };

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public static getDisplayValueFromParams = function (params: any): any {
        if (params.value !== null && typeof params.value === 'object' && params.value.hasOwnProperty('displayValue')) {
            return params.value.displayValue;
        }
        return StingerSoftAggrid.getValueFromParams(params);
    };

    private fetchRowsViaAjax(): JQuery.jqXHR {
        const that = this;
        return jQuery.getJSON(this.options.stinger.ajaxUrl, {
            'agGrid': {
                'gridId': `#${that.gridId}`,
            },
            ...this.options.stinger.additionalAjaxRequestBody
        }, (data) => {
            that.api!.setGridOption("rowData", data.items);
        });
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    private getEnterpriseDatasource(): any {
        const that = this;
        return {
            url: this.options.stinger.ajaxUrl,
            ajaxReq: null,
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            getRows: function (this: any, params: any) {
                const searchString = that.quickFilterSearchString || '';
                const requestObject = params.request;
                requestObject.search = searchString;
                requestObject.gridId = `#${that.gridId}`;
                that.api!.showLoadingOverlay();
                 
                this.ajaxReq = jQuery.post(this.url, {
                    'agGrid': requestObject,
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                }, (data: any) => {
                    if(typeof params.successCallback === "function") {
                        params.successCallback(data.items, data.total);
                    } else if (typeof params.success === "function") {
                        params.success({rowData: data.items, total: data.total});
                    }
                    that.api!.hideOverlay();
                }, "json").fail(() => {
                    that.api!.hideOverlay();
                    if(typeof params.failCallback === "function") {
                        params.failCallback();
                    } else if (typeof params.fail === "function") {
                        params.fail();
                    }
                });
            }
        }
    }

    private handleOptions(): void {
        this.isServerSide = false;
        if (this.options.stinger.dataMode === 'ajax') {
            this.fetchRowsViaAjax();
        }
        if (this.options.stinger.dataMode === 'enterprise') {
            this.isServerSide = true;
            this.api!.setGridOption('serverSideDatasource', this.getEnterpriseDatasource());
        }
        if (this.options.stinger.defaultOrderProperties) {
            const orderColumns = this.options.stinger.defaultOrderProperties || [];
            const newSortState: ColumnState[] = [];
            for (const path of Object.keys(orderColumns)) {
                const column = this.api!.getColumn(path);
                if (column !== null) {
                    newSortState.push({colId: path, sort: orderColumns[path] || 'asc'});
                }
            }
            this.api!.applyColumnState({
                state: newSortState,
                defaultState: {sort: null},
            });
        } else if (this.options.hasOwnProperty('defaultOrderProperty')) {
            const column = this.api?.getColumn(this.options.stinger.defaultOrderProperty);
            if (column !== null) {
                this.api!.applyColumnState({
                    state: [{
                        colId: this.options.stinger.defaultOrderProperty,
                        sort: this.options.stinger.defaultOrderDirection ? this.options.stinger.defaultOrderDirection : 'asc'
                    }],
                    defaultState: {sort: null},
                });
            }
        }
    }

    private registerDefaultListeners(): void {
        this.api!.addEventListener('columnResized', (event: ColumnResizedEvent) => {
            if (event.source === 'uiColumnDragged') {
                for (const column of event.columns!) {
                    this.addResizedColumn(column);
                }
            }
        });
    }

    public registerjQueryListeners(): void {
        const that = this;

        let searchField: JQuery | undefined;
        if (this.options.stinger.searchEnabled) {
            searchField = jQuery(`#${this.gridId}_search`);
            searchField.on('input keyup change', function () {
                const value = jQuery(this).val();
                that.quickFilter(String(value));
            });
        }

        let paginationDropdown: JQuery | undefined;
        if (this.options.aggrid.hasOwnProperty('pagination') && this.options.aggrid.pagination) {
            paginationDropdown = jQuery(`#${this.gridId}_paginationDropdown`);
            paginationDropdown.on('change', function () {
                const value = jQuery(this).val();
                that.api?.setGridOption('paginationPageSize', Number(value));
            });
        }

        if (this.options.stinger.hasOwnProperty('clearFilterButton') && this.options.stinger.clearFilterButton) {
            jQuery(`#${this.gridId}_clear`).on('click', () => {
                searchField?.val('');
                that.resetFilter();
            });
        }

        if (this.options.stinger.hasOwnProperty('reloadButton') && this.options.stinger.reloadButton) {
            jQuery(`#${this.gridId}_reload`).on('click', () => {
                that.reload();
            });
        }

        if (this.options.stinger.hasOwnProperty('autosizeColumnsButton') && this.options.stinger.autosizeColumnsButton) {
            jQuery(`#${this.gridId}_autosize`).on('click', () => {
                that.autoSizeColumns();
            });
        }

        //Save to local storage
        jQuery(this.aggridElement).on("remove", () => {
            that.saveState();
        });
        window.addEventListener("beforeunload", () => {
            that.saveState();
        });
        //Refresh
        jQuery(document).on('refresh.aggrid', () => {
            that.refresh(true);
        });

        if (this.foreignFormSelectInputId !== false && this.foreignFormSelectInputId) {
            this.api!.addEventListener('selectionChanged', (event) => {
                jQuery.proxy(StingerSoftAggrid.prototype.onRowSelected, that, event)();
            });
        }
    }

    private addResizedColumn(column: Column): void {
        if (!this.resizedColumns.includes(column)) {
            this.resizedColumns.push(column);
        }
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public setPaginationPageSize(entriesPerPage: any): void {
        this.api?.setGridOption('paginationPageSize', Number(entriesPerPage));
    }

    public reload(): void {
        if (this.options.stinger.hasOwnProperty('dataMode') && this.options.stinger.dataMode === 'ajax') {
            this.api!.showLoadingOverlay();
            this.fetchRowsViaAjax().always(() => {
                this.api!.hideOverlay();
            });
        }
        if (this.options.stinger.hasOwnProperty('dataMode') && this.options.stinger.dataMode === 'enterprise') {
            this.api?.refreshServerSide({purge: true});
        }
        this.refresh(true);
    }

    public refresh(force = false): void {
        this.api!.refreshCells({
            force
        });
    }

    public autoSizeColumnsWhenReady(): void {
        const that = this;
        const interval = setInterval(() => {
            if (that.checkIfBlocksLoaded()) {
                clearInterval(interval);
                that.autoSizeColumns();
            }
        }, 50);
    }

    public autoSizeColumns(resizeWithWidthSpecified?: boolean, resizeManuallyResized?: boolean): void {
        const shouldResizeFixed = resizeWithWidthSpecified === undefined ? this.options.stinger.autoResizeFixedWidthColumns : resizeWithWidthSpecified;
        const shouldResizeManual = resizeManuallyResized === undefined ? this.options.stinger.autoResizeManuallyResizedColumns : resizeManuallyResized;

        const columnIdsToResize: string[] = [];
        this.api!.getAllGridColumns().forEach((column: Column) => {
            const columnWasManuallyResized = this.resizedColumns.includes(column);
            if (columnWasManuallyResized && !shouldResizeManual) {
                return;
            }
            const columnHasWidthSpecified = "width" in column.getColDef();
            if (columnHasWidthSpecified && !shouldResizeFixed) {
                this.api!.setColumnWidth(column, column.getColDef().width!);
            } else {
                columnIdsToResize.push(column.getColId());
            }
        });
        if (columnIdsToResize.length > 0) {
            this.api!.autoSizeColumns(columnIdsToResize);
        }
    }

    /**
     * Adds all selected ids (if any) to a given foreign select form input field (if any)
     */
    protected onRowSelected(): void {
        if (this.foreignFormSelectInputId) {
            const $field = jQuery(`#${String(this.foreignFormSelectInputId)}`);
            if ($field.length > 0) {
                $field.val(Object.values(this.getSelectedIds()).join(','));
                $field.change();
            }
        }
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public getSelectedIds(field?: string): any[] {
        const selectedField = field || "id";
        const selectedRows = this.api!.getSelectedRows();
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        const selectedIds: any[] = [];
        selectedRows.forEach((selectedRow) => {
            if (selectedField in selectedRow) {
                selectedIds.push(selectedRow[selectedField].value);
            }
        });
        return selectedIds;
    }

    private readonly checkIfBlocksLoaded = (): boolean => {
        if (this.api!.getCacheBlockState() === null) {
            return false;
        }

        const cacheState = this.api!.getCacheBlockState();
        const status = cacheState[0]
            ? cacheState[0].pageStatus
            : false;
        return status === 'loaded';
    }

    private setQuickFilter(filterText: string): void {
        this.api!.setGridOption('quickFilterText', filterText);
    }

    public resetFilter(): void {
        if (this.isServerSide) {
            this.quickFilterSearchString = '';
        } else {
            this.setQuickFilter('');
        }
        this.api!.setFilterModel(null);
        this.api!.onFilterChanged();
    }

    public saveState(): void {
        if (window.localStorage && this.options.stinger.persistState && this.api!.getColumnState()) {
            const storage = window.localStorage;
            const storageKey = this.stateSavePrefix + this.stateSaveKey;
            const storageObject = {
                columns: this.api!.getColumnState(),
                groups: this.api!.getColumnGroupState(),
                sorts: this.getSortState(),
                filters: this.api!.getFilterModel(),
                version: this.options.stinger.versionHash
            };
            storage.setItem(storageKey, JSON.stringify(storageObject));
        }
    }

    protected getSortState(): Array<{colId: string, sort: string | null | undefined, sortIndex: number | null | undefined}> {
        const colState = this.api!.getColumnState();
        if(!colState) {
            return [];
        }
        return colState
            .filter((s) => s.sort != null)
            .map((s) => ({colId: s.colId, sort: s.sort, sortIndex: s.sortIndex}));
    }

    public loadState(): void {
        this.getSortState();
        if (window.localStorage && this.options.stinger.persistState) {
            const storage = window.localStorage;

            const storageKey = this.stateSavePrefix + this.stateSaveKey;
            const storageObject = JSON.parse(storage.getItem(storageKey) ?? 'null');
            if (storageObject !== null && typeof storageObject === 'object' && storageObject.hasOwnProperty('version')) {
                if (storageObject.version === this.options.stinger.versionHash) {
                    const columnState = storageObject.hasOwnProperty('columns') && storageObject.columns ? storageObject.columns : [];
                    const columnGroupState = storageObject.hasOwnProperty('groups') && storageObject.groups ? storageObject.groups : [];
                    const sortModel = storageObject.hasOwnProperty('sorts') && storageObject.sorts ? storageObject.sorts : [];
                    const filterModel = storageObject.hasOwnProperty('filters') && storageObject.filters ? storageObject.filters : {};
                    if (columnState && Array.isArray(columnState) && columnState.length) {
                        this.api!.applyColumnState({state: columnState});
                        this.api!.applyColumnState({state: columnState, applyOrder: true});
                    }
                    if (columnGroupState && Array.isArray(columnGroupState) && columnGroupState.length) {
                        this.api!.setColumnGroupState(columnGroupState);
                    }
                    if (sortModel && Array.isArray(sortModel) && sortModel.length) {
                        this.api!.applyColumnState({
                            state: sortModel,
                            defaultState: {sort: null},
                        });
                    }
                    if (filterModel && Object.keys(filterModel).length !== 0) {
                        this.api!.setFilterModel(filterModel);
                    }
                }
            }
        }
    }

    public quickFilter(searchString: string): void {
        if (!this.options.stinger.searchEnabled) {
            console.warn('search is not enabled!');
        }
        const that = this;
        if (this.filterTimeoutHandle) {
            clearTimeout(this.filterTimeoutHandle);
        }
        this.filterTimeoutHandle = setTimeout(() => {
            if (searchString === that.quickFilterSearchString) {
                return;
            }
            that.quickFilterSearchString = searchString;
            if (that.isServerSide) {
                that.api!.onFilterChanged();
            } else {
                that.setQuickFilter(searchString);
            }
        }, this.filterTimeout);
    }

    public addExportableColumn(colId: string, params: {exportValueFormatter?: string}): void {
        this.exportableColumns[colId] = params || {};
    }

    public exportXlsx(fileName: string, sheetName: string): void {
        const that = this;
        const excelParams: ExcelExportParams = {
            fileName,
            sheetName,
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            processCellCallback: function (cellParams: any) {
                let columnConfig: {exportValueFormatter?: string} = {};
                if (that.exportableColumns.hasOwnProperty(cellParams.column.getColId())) {
                    columnConfig = that.exportableColumns[cellParams.column.getColId()];
                }
                const valueGetter = columnConfig.hasOwnProperty('exportValueFormatter') && columnConfig.exportValueFormatter ? StingerSoftAggrid.Formatter.getFormatter(columnConfig.exportValueFormatter) : StingerSoftAggrid.Formatter.getFormatter("DisplayValueFormatter");
                return valueGetter(cellParams);
            }
        };

        excelParams.columnKeys = Object.keys(this.exportableColumns);
        this.api!.exportDataAsExcel(excelParams);
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public processCellForClipboard(params: ProcessCellForExportParams): any {
        let value = params.value.displayValue;
        const callbackName = this.clipboardValueFormatters.hasOwnProperty(params.column.getColId()) ?
            this.clipboardValueFormatters[params.column.getColId()] : false;
        if(callbackName === false) {
            value = params.value.displayValue;
        }
        if(typeof callbackName === 'string') {
            const renderer = StingerSoftAggrid.Formatter.getFormatter(callbackName);
            value = renderer(params);
        }
        value = typeof value === "string" ? value.trim() : value;
        return value;
    }

    public setClipboardValueFormatter(colId: string, callback: string): void {
        if(!this.clipboardValueFormatters.hasOwnProperty(colId)) {
            this.clipboardValueFormatters[colId] = callback;
        }
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    protected static processJsonColumnConfiguration(column: any, configuration: GridConfiguration): any {
        if (column.hasOwnProperty('render_html') && column.render_html) {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            column.cellRenderer = (params: any) => params.value ? params.value : ''
        }

        if (column.hasOwnProperty('cellRenderer') && column.cellRenderer) {
            column.cellRenderer = StingerSoftAggrid.Renderer.getRenderer(column.cellRenderer, column.cellRendererParams || {});
        }
        if (column.hasOwnProperty('valueGetter') && column.valueGetter) {
            column.valueGetter = StingerSoftAggrid.Getter.getGetter(column.valueGetter, column.valueGetterParams || {});
        }
        if (column.hasOwnProperty('filterValueGetter') && column.filterValueGetter) {
            column.filterValueGetter = StingerSoftAggrid.Getter.getGetter(column.filterValueGetter, column.filterValueGetterParams || {});
        }

        if (column.hasOwnProperty('valueSetter') && column.valueSetter) {
            // intentionally empty - valueSetter handling placeholder
        }
        if (column.hasOwnProperty('valueFormatter') && column.valueFormatter) {
            column.valueFormatter = StingerSoftAggrid.Formatter.getFormatter(column.valueFormatter, column.valueFormatterParams || {});
        }

        if (column.hasOwnProperty('comparator') && column.comparator) {
            column.comparator = StingerSoftAggrid.Comparator.getComparator(column.comparator);
        }
        if (column.hasOwnProperty('getQuickFilterText') && column.getQuickFilterText) {
            column.getQuickFilterText = StingerSoftAggrid.Filter.getFilter(column.getQuickFilterText, configuration.stinger.dataMode);
        } else if (column.hasOwnProperty('valueGetter') && column.valueGetter) {
            column.getQuickFilterText = column.valueGetter;
        } else if (column.hasOwnProperty('valueFormatter') && column.valueFormatter) {
            column.getQuickFilterText = column.valueFormatter;
        } else {
            column.getQuickFilterText = StingerSoftAggrid.Formatter.getFormatter('DefaultFormatter');
        }

        if (column.hasOwnProperty('keyCreator') && column.keyCreator) {
            column.keyCreator = StingerSoftAggrid.Creator.getKeyCreator(column.keyCreator);
        }
        if (column.hasOwnProperty('tooltip') && column.tooltip) {
            column.tooltip = StingerSoftAggrid.Tooltip.getTooltip(column.tooltip);
        }
        if (column.hasOwnProperty('checkboxSelection') && column.checkboxSelection !== true && column.checkboxSelection !== false) {
            console.warn('Passing a callable via JSON configuration for the field [checkboxSelection] is not supported!');
            delete column.checkboxSelection;
        }

        if (column.hasOwnProperty('valueFormatterParams')) {
            delete column.valueFormatterParams;
        }

        if (column.hasOwnProperty('filterParams')) {
            if (column.filterParams?.hasOwnProperty('cellRenderer')) {
                column.filterParams.cellRenderer = StingerSoftAggrid.Renderer.getRenderer(column.filterParams.cellRenderer, column.filterParams.cellRendererParams || {});
            }
            if (column.filterParams?.hasOwnProperty('textFormatter')) {
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                column.filterParams.textFormatter = function (this: any, value: any) {
                    const formatter = StingerSoftAggrid.TextFormatter.getFormatter(column.filterParams.textFormatter, column.filterParams.textFormatterParams || {});
                    return formatter(value, this.colDef);
                }
            }
        }
        if (column.hasOwnProperty('children')) {
            for (const childColumn of (column as ColGroupDef).children) {
                StingerSoftAggrid.processJsonColumnConfiguration(childColumn, configuration);
            }
        }
        return column;
    }

    public static processJsonConfiguration(configuration: GridConfiguration, translator: BazingaTranslator|null = null): void {
        for (const column of configuration.aggrid.columnDefs!) {
            StingerSoftAggrid.processJsonColumnConfiguration(column, configuration);
        }
        configuration.aggrid.getLocaleText = function (params: GetLocaleTextParams) {
            const {key} = params;
            const {defaultValue} = params;
            const gridKey = `stingersoft_aggrid.${key}`;
            const value = (translator ?? Translator).trans(gridKey, {}, 'StingerSoftAggridBundle');
            if (value === gridKey) {
                console.debug(`falling back to default value "${defaultValue}", as no translation was found for "${key}" (tried "${gridKey}" within the domain "StingerSoftAggridBundle"!`);
                return defaultValue;
            }
            return value;
        }
    }


}
