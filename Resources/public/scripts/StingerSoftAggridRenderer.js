/**
 * All renderers follow the same principal:
 * The are initialized and can override
 * - init
 * - getGui
 * - refresh
 * - destroy
 * Only refresh may be called more than once.
 * All other functions are called only once.
 * Therefore, refresh has to work with the existing element
 * and may refresh that, but cannot recreate it as getGui
 * is only called once.
 *
 * Renderers differentiate from Formatters as they should
 * return an HTML element, whereas formatters should only
 * alter the value.
 */

/**
 * @return {function(*): string}
 */
StingerSoftAggrid.Renderer.RawHtmlRenderer = function(rendererParams) {
	return function(params) {
		return params.value ? params.value : '';
	};
};

/**
 *
 * @returns StingerSoftAggrid.Renderer.AbridgedRenderer
 */
StingerSoftAggrid.Renderer.AbridgedRenderer =  function(rendererParams) {
	this.eGui = document.createElement('abbr');
};

/**
 *
 * @param params
 */
StingerSoftAggrid.Renderer.AbridgedRenderer.prototype.init = function(params) {
	if(params.value !== "" && params.value !== undefined && params.value !== null) {
		var $td = jQuery(this.eGui);
		$td.attr('data-toggle', 'tooltip');
		$td.attr('data-container', 'body');
		$td.attr('data-placement', 'left');
		$td.attr('title', params.value);
		this.eGui.innerHTML = params.value;
	}
};

/**
 *
 * @returns {HTMLElement | *}
 */
StingerSoftAggrid.Renderer.AbridgedRenderer.prototype.getGui = function() {
	return this.eGui;
};

/**
 *
 * @param params
 */
StingerSoftAggrid.Renderer.AbridgedRenderer.prototype.refresh = function(params) {
	this.init(params);
};

/**
 * @returns StingerSoftAggrid.Renderer.YesNoRenderer
 * @constructor
 */
StingerSoftAggrid.Renderer.YesNoRenderer = function(rendererParams) {
	//"Constants"
	this.TYPE_ICON_ONLY = 0;
	this.TYPE_ICON_TOOLTIP = 1;
	this.TYPE_ICON_TEXT = 2;
	this.TYPE_TEXT_ONLY = 3;

	this.noValue = 0;
	this.yesValue = 1;

	this.noIconClass = "fas fa-times";
	this.yesIconClass = "fas fa-check";

	this.eGui = document.createElement('i');
};

/**
 *
 * @param params
 */
StingerSoftAggrid.Renderer.YesNoRenderer.prototype.init = function(params) {
	if(params.value !== "" && params.value !== undefined && params.value !== null) {
		StingerSoft.mapValuesToObject(params, this);

		if(params.value == this.noValue) {
			this.eGui.className = this.noIconClass;
		} else if(params.value == this.yesValue) {
			this.eGui.className = this.yesIconClass;
		}
	}
};

/**
 *
 * @returns {HTMLElement | *}
 */
StingerSoftAggrid.Renderer.YesNoRenderer.prototype.getGui = function() {
	return this.eGui;
};

/**
 *
 * @returns StingerSoftAggrid.Renderer.ProgressBarRenderer
 */
StingerSoftAggrid.Renderer.ProgressBarRenderer = function(rendererParams) {
	this.eGui = document.createElement('div');
	this.innerDiv = document.createElement('div');

	this.min = 0;
	this.max = 100;
};

/**
 *
 * @param params
 */
StingerSoftAggrid.Renderer.ProgressBarRenderer.prototype.init = function(params) {
	if(params.value !== "" && params.value !== undefined && params.value !== null) {
		StingerSoftAggrid.mapValuesToObject(params, this);
		//Inner
		var $inner = jQuery(this.innerDiv);
		$inner.attr('role', params.value);
		$inner.attr('aria-valuenow', params.value);
		$inner.attr('aria-valuemin', this.min);
		$inner.attr('aria-valuemax', this.max);
		$inner.css('width', params.value + "%");
		this.innerDiv.className = "progress-bar";
		this.innerDiv.innerHTML = params.value + "%";

		//
		var $td = jQuery(this.eGui);
		$td.attr('title', params.value + "%");
		$td.attr('data-toggle', 'tooltip');
		$td.attr('data-container', 'body');
		$td.attr('data-placement', 'left');
		this.eGui.className = "progress";
		this.eGui.appendChild(this.innerDiv);
	}
};

/**
 *
 * @returns {HTMLElement | *}
 */
StingerSoftAggrid.Renderer.ProgressBarRenderer.prototype.getGui = function() {
	return this.eGui;
};

/**
 *
 * @param params
 */
StingerSoftAggrid.Renderer.ProgressBarRenderer.prototype.refresh = function(params) {
	this.init(params);
};

/**
 *
 * @returns StingerSoftAggrid.Renderer.UserRenderer
 */
StingerSoftAggrid.Renderer.UserRenderer = function(rendererParams) {
	this.eGui = document.createElement('abbr');
};

/**
 *
 * @param params
 */
StingerSoftAggrid.Renderer.UserRenderer.prototype.init = function(params) {
	if(params.value !== "" && params.value !== undefined && params.value !== null) {
		try {
			var userUrl = Routing.generate('pec_social_popover', {
				'user': params.value.id,
				'username': params.value.username
			});
		} catch(err) {
		}

		//
		var $abbr = jQuery(this.eGui);
		$abbr.attr('title', params.value.username + " - " + params.value.email);
		$abbr.attr('data-toggle', 'tooltip');
		$abbr.attr('data-container', 'body');
		$abbr.attr('data-placement', 'left');
		$abbr.attr('data-id', params.value.id);
		$abbr.attr('data-username', params.value.username);
		if(typeof userUrl !== "undefined") {
			this.eGui.className = "platform-user-name popover-ajax hover-stay";
			$abbr.attr('data-html', true);
			$abbr.attr('data-trigger', "hover");
			$abbr.attr('data-popover-url', userUrl);
			$abbr.attr('data-template', '<div class="popover user" role="tooltip" style="max-width: 350px; width: 350px;"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>');
		} else {
			this.eGui.className = "platform-user-name";
		}
		if("firstname" in params.value) {
			this.eGui.innerHTML = (params.value.firstname || "") + " " + (params.value.surname || "");
		} else {
			this.eGui.innerHTML = params.value.username;
		}
	}
};

/**
 *
 * @returns {HTMLElement | *}
 */
StingerSoftAggrid.Renderer.UserRenderer.prototype.getGui = function() {
	return this.eGui;
};

/**
 *
 * @param params
 */
StingerSoftAggrid.Renderer.UserRenderer.prototype.refresh = function(params) {
	this.init(params);
};


/**
 *
 * @returns StingerSoftAggrid.Renderer.UserFilterRenderer
 */
StingerSoftAggrid.Renderer.UserFilterRenderer = function(rendererParams) {
	this.eGui = document.createElement('span');
};

/**
 *
 * @param params
 */
StingerSoftAggrid.Renderer.UserFilterRenderer.prototype.init = function(params) {
	if(params.value !== "" && params.value !== undefined && params.value !== null) {
		var values = params.value.split("|");
		if(values.length > 1) {
			this.eGui.innerHTML = (values[0] && values[0] != "null" ? values[0] : "") + " " + (values[1] && values[1] != "null" ? values[1] : "");
		}
	}
};

/**
 *
 * @returns {HTMLElement | *}
 */
StingerSoftAggrid.Renderer.UserFilterRenderer.prototype.getGui = function() {
	return this.eGui;
};

/**
 *
 * @param params
 */
StingerSoftAggrid.Renderer.UserFilterRenderer.prototype.refresh = function(params) {
	this.init(params);
};