var StingerSoft = function () {
};

/**
 * Map the given values to the object properties,
 * if they exist with the same name.
 *
 * @param {array} values
 * @param {Object} object
 */
StingerSoft.mapValuesToObject = function (values, object) {
	if (typeof values !== "undefined" && values) {
		Object.keys(values).forEach(function (key) {
			if (object.hasOwnProperty(key)) {
				object[key] = values[key];
			}
		});
	}
};

/**
 * General Ag-Grid object to be initialized,
 * set and used with events or overridden by custom scripts e.g.
 *
 * <pre>
 *   function MyAggrid(gridId, ...) {
 *     StingerSoftAggrid.call(this, gridId, ...);
 *
 *     ...
 *   };
 *
 *   MyAggrid.prototype = Object.create(StingerSoftAggrid.prototype);
 *   MyAggrid.prototype.constructor = MyAggrid;
 * </pre>
 *
 * @param {string} gridId
 * @returns StingerSoftAggrid
 */
function StingerSoftAggrid(gridId) {
	/** */
	if (gridId.substring(0, 1) !== '#') {
		gridId = '#' + gridId;
	}
	this.gridId = gridId;
	/** */
	this.aggrid = document.querySelector(gridId);
	/** */
	this.$aggrid = jQuery(gridId);
	/** */
	this.licenseKey = null;
	/** */
	this.grid = null;
	/** */
	this.gridOptions = null;
	/** */
	this.options = null;

	/** */
	this.filterTimeout = 500;

	/** */
	this.stateSavePrefix = "StingerSoftAggrid_";
	this.stateSaveKey = gridId.replace("#", "");

	/** */
	this.foreignFormSelectInputId = false;
}

/**
 *
 * @param {json} gridOptions - The Ag-Grid configuration options
 * @param {json} stingerOptions - The stinger-soft Ag-Grid configuration options
 */
StingerSoftAggrid.prototype.init = function (gridOptions, stingerOptions) {
	"use strict";

	//
	this.gridOptions = gridOptions;
	this.options = stingerOptions;
	if (this.options.hasOwnProperty('enterpriseLicense')) {
		this.setLicenseKey(this.options.enterpriseLicense);
	}
	this.grid = new agGrid.Grid(this.aggrid, gridOptions);

	//Init
	this.handleOptions();
	this.registerListeners();
	return this;
};

StingerSoftAggrid.prototype.handleOptions = function () {
	if (this.options.hasOwnProperty('persistState')) {
		this.persistState = this.options.persistState;
	}
	if (this.options.hasOwnProperty('versionHash')) {
		this.versionHash = this.options.versionHash;
	}
	if (this.options.hasOwnProperty('searchEnabled')) {
		this.searchEnabled = this.options.searchEnabled;
	}
	this.isServerSide = false;
	var that = this;
	if (this.options.hasOwnProperty('dataMode') && this.options.dataMode === 'ajax') {
		this.isServerSide = true;
		if (this.options.hasOwnProperty('ajaxUrl')) {
			jQuery.getJSON(this.options.ajaxUrl, function (data) {
				that.gridOptions.api.setRowData(data.items);
			});
		}
	}
	if (this.options.hasOwnProperty('dataMode') && this.options.dataMode === 'enterprise') {
		this.isServerSide = true;
		var serverSideDatasource = {
			url: this.options.ajaxUrl,
			ajaxReq: null,
			getRows: function (params) {
				if (this.ajaxReq !== null) {
					this.ajaxReq.abort();
				}
				var searchString = that.quickFilterSearchString || '';
				var requestObject = params.request;
				requestObject['search'] = searchString;
				this.ajaxReq = jQuery.post(this.url, {
					'agGrid': JSON.stringify(requestObject),
				}, function (data) {
					params.successCallback(data.items, data.total);
				}, "json").fail(params.failCallback);
			}
		};
		this.gridOptions.api.setServerSideDatasource(serverSideDatasource);
	}
};

/**
 *
 * @param params
 */
StingerSoftAggrid.prototype.getContextMenuItems = function (params) {
	"use strict";
	return [
		'expandAll',
		'contractAll',
		'separator',
		'copy',
		'copyWithHeaders'
	];
};

/**
 *
 * @param {string} field
 * @param {array} values
 */
StingerSoftAggrid.prototype.filter = function (field, values) {
	"use strict";

	var gridApi = this.getGridApi();
	var filter = gridApi.getFilterInstance(field);
	if (filter) {
		//Reset
		filter.selectNothing();
		//Select values
		for (var i = 0; i < values.length; i++) {
			filter.selectValue(values[i]);
		}
	}
	//Apply
	gridApi.onFilterChanged();
};

/**
 *
 * @param {string} searchString
 */
StingerSoftAggrid.prototype.quickFilter = function (searchString) {
	if (!this.searchEnabled) {
		console.warn('search is not enabled!');
	}
	var that = this;
	if (this.filterTimeoutHandle) {
		clearTimeout(this.filterTimeoutHandle);
	}
	this.filterTimeoutHandle = setTimeout(function () {
		if (searchString === that.quickFilterSearchString) {
			return;
		}
		that.quickFilterSearchString = searchString;
		if (that.isServerSide) {
			that.gridOptions.api.onFilterChanged();
		} else {
			that.gridOptions.api.setQuickFilter(searchString);
		}
	}, this.filterTimeout);
};

/**
 * Reset all filter
 */
StingerSoftAggrid.prototype.resetFilter = function () {
	if (this.isServerSide) {
		this.quickFilterSearchString = '';
	} else {
		this.gridOptions.api.setQuickFilter();
	}
	this.gridOptions.api.setFilterModel(null);
	this.gridOptions.api.onFilterChanged();
};

/**
 * Reset all sorting
 */
StingerSoftAggrid.prototype.resetSort = function () {
	this.gridOptions.api.setSortModel(null);
};

/**
 * Register some commonly used listeners.
 */
StingerSoftAggrid.prototype.registerListeners = function () {
	var that = this;
	if (this.searchEnabled) {
		this.$searchField = jQuery(this.gridId + '_search');
		this.$searchField.on('input keyup change', function () {
			var value = jQuery(this).val();
			that.quickFilter(value);
		});
	}
	if (this.gridOptions.hasOwnProperty('pagination') && this.gridOptions.pagination) {
		this.$paginationDropdown = jQuery(this.gridId + '_paginationDropdown');
		this.$paginationDropdown.on('change', function () {
			var value = jQuery(this).val();
			that.getGridApi().paginationSetPageSize(Number(value));
		});
	}

	//Save to local storage
	this.$aggrid.on("remove", function () {
		that.save();
	});
	window.addEventListener("beforeunload", function () {
		that.save();
	});
	//Refresh
	jQuery(document).on('refresh.aggrid', function () {
		that.refresh(true);
	});
};

/**
 *
 * @param colDef
 * @param refresh
 */
StingerSoftAggrid.prototype.setColumnDefs = function (colDef, refresh) {
	if (refresh === undefined) {
		refresh = false;
	}
	this.gridOptions.api.setColumnDefs(colDef);
	if (refresh) {
		this.refresh();
	}
};

/**
 * Update the grid data option.
 * Call refresh to use the updated data.
 *
 * @param {Array} data The new data
 * @param {boolean} refresh Defaults to false
 */
StingerSoftAggrid.prototype.setData = function (data, refresh) {
	if (refresh === undefined) {
		refresh = false;
	}
	this.gridOptions.api.setRowData(data);
	if (refresh) {
		this.refresh();
	}
};

/**
 *
 * @param {boolean} force If true refreshes all cells and does not compare. Defaults to false
 */
StingerSoftAggrid.prototype.refresh = function (force) {
	if (force === undefined) {
		force = false;
	}
	this.gridOptions.api.refreshCells({
		"force": force
	});
};

/**
 *
 * @param {agGrid.ColumnApi} columnApi
 * @param {agGrid.GridApi} gridApi
 */
StingerSoftAggrid.prototype.save = function (columnApi, gridApi) {
	if (window.localStorage && this.persistState) {
		var storage = window.localStorage;
		var _columnApi = this.getColumnApi(columnApi);
		var _gridApi = this.getGridApi(gridApi);

		var storageKey = this.stateSavePrefix + this.stateSaveKey;
		var storageObject = {
			columns: _columnApi.getColumnState(),
			groups: _columnApi.getColumnGroupState(),
			sorts: _gridApi.getSortModel(),
			filters: _gridApi.getFilterModel(),
			version: this.versionHash
		};
		storage.setItem(storageKey, JSON.stringify(storageObject));
	}
};

/**
 *
 * @param {agGrid.ColumnApi} columnApi
 * @param {agGrid.GridApi} gridApi
 */
StingerSoftAggrid.prototype.load = function (columnApi, gridApi) {
	if (window.localStorage && this.persistState) {
		var storage = window.localStorage;
		var _columnApi = this.getColumnApi(columnApi);
		var _gridApi = this.getGridApi(gridApi);

		var storageKey = this.stateSavePrefix + this.stateSaveKey;
		var storageObject = JSON.parse(storage.getItem(storageKey));
		if (typeof storageObject === 'object' && storageObject.hasOwnProperty('version')) {
			if (storageObject.version === this.versionHash) {
				var columnState = storageObject.hasOwnProperty('columns') && storageObject.columns ? storageObject.columns : [];
				var columnGroupState = storageObject.hasOwnProperty('groups') && storageObject.groups ? storageObject.groups : [];
				var sortModel = storageObject.hasOwnProperty('sorts') && storageObject.sorts ? storageObject.sorts : [];
				var filterModel = storageObject.hasOwnProperty('filters') && storageObject.filters ? storageObject.filters : {};
				if (columnState && Array.isArray(columnState) && columnState.length) {
					_columnApi.setColumnState(columnState);
				}
				if (columnGroupState && Array.isArray(columnGroupState) && columnGroupState.length) {
					_columnApi.setColumnGroupState(columnGroupState);
				}
				if (sortModel && Array.isArray(sortModel) && sortModel.length) {
					_gridApi.setSortModel(sortModel);
				}
				if (filterModel && Object.keys(filterModel).length !== 0) {
					_gridApi.setFilterModel(filterModel);
				}
			}
		}
	}
};

/**
 *
 * @param gridApi
 * @param field Defaults to "id"
 * @returns {Array} The selected ids, if the row have the data field
 */
StingerSoftAggrid.prototype.getSelectedIds = function (gridApi, field) {
	var _field = field || "id";
	var _gridApi = this.getGridApi(gridApi);
	var selectedRows = _gridApi.getSelectedRows();
	var selectedIds = [];
	selectedRows.forEach(function (selectedRow, index) {
		if (_field in selectedRow) {
			selectedIds.push(selectedRow[field]);
		}
	});
	return selectedIds;
};

/**
 * Adds all selected ids (if any) to a given foreign select form input field (if any)
 *
 * @param event
 */
StingerSoftAggrid.prototype.onRowSelected = function (event) {
	if (this.foreignFormSelectInputId) {
		var $field = jQuery('#' + this.foreignFormSelectInputId);
		if ($field.length > 0) {
			$field.val(Object.values(this.getSelectedIds(event.api)).join(','));
		}
	}
};

/**
 * Return the given columnApi (if any) or return the global api.
 *
 * @param columnApi
 * @returns {agGrid.ColumnApi}
 */
StingerSoftAggrid.prototype.getColumnApi = function (columnApi) {
	return columnApi && columnApi instanceof agGrid.ColumnApi ? columnApi : this.gridOptions.columnApi;
};

/**
 *
 * @param gridApi
 * @returns {agGrid.GridApi}
 */
StingerSoftAggrid.prototype.getGridApi = function (gridApi) {
	return gridApi && gridApi instanceof agGrid.GridApi ? gridApi : this.gridOptions.api;
};

/**
 * @return {string}
 */
StingerSoftAggrid.prototype.getGridId = function () {
	return this.gridId;
};

/**
 * @return {Element}
 */
StingerSoftAggrid.prototype.getAggrid = function () {
	return this.aggrid;
};

/**
 * @return {jQuery}
 */
StingerSoftAggrid.prototype.getAggridJquery = function () {
	return this.$aggrid;
};

/**
 * @return {Grid}
 */
StingerSoftAggrid.prototype.getGrid = function () {
	return this.grid;
};

/**
 * @return {string}
 */
StingerSoftAggrid.prototype.getLicenseKey = function () {
	return this.licenseKey;
};

/**
 * @param {string} licenseKey
 */
StingerSoftAggrid.prototype.setLicenseKey = function (licenseKey) {
	this.licenseKey = licenseKey;
	agGrid.LicenseManager.setLicenseKey(licenseKey);
	return this.licenseKey;
};

/**
 * @return {Object}
 */
StingerSoftAggrid.prototype.getGridOptions = function () {
	return this.gridOptions;
};

/**
 * The Namespace for all renderers.
 * Custom renderers have to be "registered" to this namespace.
 */
StingerSoftAggrid.Renderer = StingerSoftAggrid.Renderer || {};

/**
 *
 * @param {string} renderer - The name of the renderer function to pull
 * @param {json} rendererParams
 * @returns {*} The according renderer or default to the normal renderer
 */
StingerSoftAggrid.Renderer.getRenderer = function (renderer, rendererParams) {
	//Default to null -> Uses the default renderer
	var aggridRenderer = null;
	if (renderer in StingerSoftAggrid.Renderer && typeof StingerSoftAggrid.Renderer[renderer] == 'function') {
		aggridRenderer = StingerSoftAggrid.Renderer[renderer];
	} else {
		console.warn('Renderer "' + renderer + '" not found! Returning agGrid default function');
	}
	return aggridRenderer;
};

/**
 * The Namespace for all formatters.
 * Custom formatters have to be "registered" to this namespace.
 */
StingerSoftAggrid.Formatter = StingerSoftAggrid.Formatter || {};

/**
 *
 * @param {string} formatter - The name of the formatter function to pull
 * @param {json} formatterParams
 * @returns {*} The according formatter or default to the normal formatter
 */
StingerSoftAggrid.Formatter.getFormatter = function (formatter, formatterParams) {
	//Default to null -> Uses the default formatter
	var aggridFormatter = null;
	if (formatter in StingerSoftAggrid.Formatter && typeof StingerSoftAggrid.Formatter[formatter] == 'function') {
		var finalFormatterParams = formatterParams || {};
		aggridFormatter = StingerSoftAggrid.Formatter[formatter](finalFormatterParams);
	} else {
		console.warn('Formatter "' + formatter + '" not found! Returning agGrid default function');
	}
	return aggridFormatter;
};

/**
 * The Namespace for all editors.
 * Custom editors have to be "registered" to this namespace.
 */
StingerSoftAggrid.Editor = StingerSoftAggrid.Editor || {};

/**
 *
 * @param {string} editor - The name of the editor function to pull
 * @param {json} editorParams
 * @returns {*} The according editor or default null
 */
StingerSoftAggrid.Editor.getEditor = function (editor, editorParams) {
	//Default to null -> Uses the default editor
	var aggridEditor = null;
	if (editor in StingerSoftAggrid.Editor && typeof StingerSoftAggrid.Editor[editor] == 'function') {
		var finalEditorParams = editorParams || {};
		aggridEditor = StingerSoftAggrid.Editor[editor](finalEditorParams);
	} else {
		console.warn('Editor "' + editor + '" not found! Returning agGrid default function');
	}
	return aggridEditor;
};


/**
 * The Namespace for all getters.
 * Custom getters have to be "registered" to this namespace.
 */
StingerSoftAggrid.Getter = StingerSoftAggrid.Getter || {};

/**
 *
 * @param {string} getter - The name of the getter function to pull
 * @param {json} getterParams
 * @returns {*} The according getter or default to the normal formatter
 */
StingerSoftAggrid.Getter.getGetter = function (getter, getterParams) {
	//Default to null -> Uses the default getter
	var aggridGetter = null;
	if (getter in StingerSoftAggrid.Getter && typeof StingerSoftAggrid.Getter[getter] == 'function') {
		var finalGetterParams = getterParams || {};
		aggridGetter = StingerSoftAggrid.Getter[getter](getterParams);
	} else {
		console.warn('Getter "' + getter + '" not found! Returning agGrid default function');
	}
	return aggridGetter;
};

/**
 * The Namespace for all keyCreators.
 * Custom keyCreators have to be "registered" to this namespace.
 */
StingerSoftAggrid.Creator = StingerSoftAggrid.Creator || {};

/**
 *
 * @param {string} keyCreator - The name of the keyCreator function to pull
 * @param {json} keyCreatorParams
 * @returns {*} The according keyCreator or default to the normal formatter
 */
StingerSoftAggrid.Creator.getKeyCreator = function (keyCreator, keyCreatorParams) {
	//Default to null -> Uses the default creator
	var aggridKeyCreator = null;
	if (keyCreator in StingerSoftAggrid.Creator && typeof StingerSoftAggrid.Creator[keyCreator] == 'function') {
		var finalKeyCreatorParams = keyCreatorParams || {};
		aggridKeyCreator = StingerSoftAggrid.Creator[keyCreator](finalKeyCreatorParams);
	} else {
		console.warn('Key Creator "' + keyCreator + '" not found! Returning agGrid default function');
	}
	return aggridKeyCreator;
};

/**
 * The Namespace for all tooltip rendererd.
 * Custom tooltips have to be "registered" to this namespace.
 */
StingerSoftAggrid.Tooltip = StingerSoftAggrid.Tooltip || {};

/**
 *
 * @param {string} tooltip - The name of the tooltip function to pull
 * @param {json} tooltipParams
 * @returns {*} The according tooltip or default to null
 */
StingerSoftAggrid.Tooltip.getTooltip = function (tooltip, tooltipParams) {
	//Default to null -> Uses the default tooltip
	var aggridTooltip = null;
	if (tooltip in StingerSoftAggrid.Tooltip && typeof StingerSoftAggrid.Tooltip[tooltip] == 'function') {
		var finalTooltipParams = tooltipParams || {};
		aggridTooltip = StingerSoftAggrid.Tooltip[tooltip](finalTooltipParams);
	} else {
		console.warn('Tooltip "' + tooltip + '" not found! Returning agGrid default function');
	}
	return aggridTooltip;
};

/**
 * The Namespace for all filter renderer.
 * Custom filters have to be "registered" to this namespace.
 */
StingerSoftAggrid.Filter = StingerSoftAggrid.Filter || {};

/**
 *
 * @param {string} filter - The name of the filter function to pull
 * @param {json} filterParams
 * @returns {*} The according filter or default to null
 */
StingerSoftAggrid.Filter.getFilter = function (filter, filterParams) {
	//Default to null -> Uses the default filter
	var aggridFilter = null;
	if (filter in StingerSoftAggrid.Filter && typeof StingerSoftAggrid.Filter[filter] == 'function') {
		var finalFilterParams = filterParams || {};
		aggridFilter = StingerSoftAggrid.Filter[filter](finalFilterParams);
	} else {
		console.warn('Filter "' + filter + '" not found! Returning agGrid default function');
	}
	return aggridFilter;
};

/**
 * The Namespace for all stylers.
 * Custom stylers have to be "registered" to this namespace.
 */
StingerSoftAggrid.Styler = StingerSoftAggrid.Styler || {};

/**
 *
 * @param {string} styler - The name of the styler function to pull
 * @returns {*} The according styler or default to the normal NoOp styler
 */
StingerSoftAggrid.Styler.getStyler = function (styler) {
	if (styler in StingerSoftAggrid.Styler && typeof StingerSoftAggrid.Styler[styler] == 'function') {
		return StingerSoftAggrid.Styler[styler]();
	}
	console.warn('Styler "' + styler + '" not found! Returning empty function');
	return StingerSoftAggrid.Styler.NoOp();
};