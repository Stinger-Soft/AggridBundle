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

(function (factory) {
    "use strict";

    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery'], function (jQuery) {
            return factory(jQuery, window, document);
        });
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, jQuery) {
            if (!root) {
                // CommonJS environments without a window global must pass a
                // root. This will give an error otherwise
                root = window;
            }

            if (!jQuery) {
                jQuery = typeof window !== 'undefined' ? // jQuery's factory checks for a global window
                    require('jquery') :
                    require('jquery')(root);
            }
            return factory(jQuery, root, root.document);
        };
    } else {
        // Browser
        factory(jQuery, window, document);
    }
}
(function (jQuery, window, document, undefined) {

    /**
     * @return {function(*): string}
     */
    StingerSoftAggrid.Renderer.RawHtmlRenderer = function (params) {
        return params.value ? params.value : '';
    };

    StingerSoftAggrid.Renderer.KeyValueMappingRenderer = function (rendererParams) {
        var val = rendererParams.value;
        var translationDomain = rendererParams.hasOwnProperty('translation_domain') && rendererParams.translation_domain ? rendererParams.translation_domain : 'messages';
        var keyValueMapping = rendererParams.hasOwnProperty('keyValueMapping') && rendererParams.keyValueMapping ? rendererParams.keyValueMapping : {};
        if (val && keyValueMapping.hasOwnProperty(val)) {
            if (translationDomain) {
                return Translator.trans(keyValueMapping[val], {}, translationDomain);
            }
            return keyValueMapping[val];
        }
        return val ? val : '';
    };

    /**
     *
     * @returns StingerSoftAggrid.Renderer.AbridgedRenderer
     */
    StingerSoftAggrid.Renderer.AbridgedRenderer = function () {
        this.eGui = document.createElement('abbr');
    };

    /**
     *
     * @param params
     */
    StingerSoftAggrid.Renderer.AbridgedRenderer.prototype.init = function (params) {
        if (params.value !== "" && params.value !== undefined && params.value !== null) {
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
    StingerSoftAggrid.Renderer.AbridgedRenderer.prototype.getGui = function () {
        return this.eGui;
    };

    /**
     *
     * @param params
     */
    StingerSoftAggrid.Renderer.AbridgedRenderer.prototype.refresh = function (params) {
        this.init(params);
    };

    /**
     * @returns StingerSoftAggrid.Renderer.YesNoRenderer
     * @constructor
     */
    StingerSoftAggrid.Renderer.YesNoRenderer = function () {
        //"Constants"
        this.TYPE_ICON_ONLY = 'icon-only';
        this.TYPE_ICON_TOOLTIP = 'icon-with-tooltip';
        this.TYPE_ICON_WITH_LABEL = 'icon-with-label';
        this.TYPE_LABEL_ONLY = 'label-only';

        this.noValue = false;
        this.yesValue = true;

        this.noIconClass = '';
        this.yesIconClass = '';

        this.noLabel = '';
        this.yesLabel = '';
        this.eGui = document.createElement('span');
        return this;
    };

    /**
     *
     * @param params
     */
    StingerSoftAggrid.Renderer.YesNoRenderer.prototype.init = function (params) {
        this.noIconClass = params.no_icon;
        this.yesIconClass = params.yes_icon;

        this.noLabel = params.no_label;
        this.yesLabel = params.yes_label;

        if (params.value !== "" && params.value !== undefined && params.value !== null) {
            StingerSoft.mapValuesToObject(params, this);

            var value = params.value;
            value = value === 'true' ? true : value;
            value = value === 'false' ? false : value;

            if (params.display_type !== this.TYPE_LABEL_ONLY) {
                this.eGui.innerHTML = "<i></i>";
                this.icon = this.eGui.querySelector('i');
                if (value == this.noValue) {
                    this.icon.className = this.noIconClass;
                } else if (value == this.yesValue) {
                    this.icon.className = this.yesIconClass;
                }
            }
            if (params.display_type === this.TYPE_LABEL_ONLY || params.display_type === this.TYPE_ICON_WITH_LABEL) {
                if (value == this.noValue) {
                    this.textnode = document.createTextNode(this.noLabel);
                    this.eGui.appendChild(this.textnode);
                } else if (value == this.yesValue) {
                    this.textnode = document.createTextNode(this.yesLabel);
                    this.eGui.appendChild(this.textnode);
                }
            }
            if (params.display_type === this.TYPE_ICON_TOOLTIP) {
                this.icon.setAttribute("data-toggle", "tooltip");
                this.icon.setAttribute("data-container", "body");
                if (value == this.noValue) {
                    this.icon.setAttribute("title", this.noLabel);
                } else if (value == this.yesValue) {
                    this.icon.setAttribute("title", this.yesLabel);
                }
            }
        }
    };

    /**
     *
     * @param params
     */
    StingerSoftAggrid.Renderer.YesNoRenderer.prototype.refresh = function (params) {
        this.init(params);
    };

    /**
     *
     * @returns {HTMLElement | *}
     */
    StingerSoftAggrid.Renderer.YesNoRenderer.prototype.getGui = function () {
        return this.eGui;
    };

    /**
     *
     * @returns StingerSoftAggrid.Renderer.ProgressBarRenderer
     */
    StingerSoftAggrid.Renderer.ProgressBarRenderer = function (rendererParams) {
        this.eGui = document.createElement('div');
        this.innerDiv = document.createElement('div');

        this.min = 0;
        this.max = 100;
    };

    /**
     *
     * @param params
     */
    StingerSoftAggrid.Renderer.ProgressBarRenderer.prototype.init = function (params) {
        if (params.value !== "" && params.value !== undefined && params.value !== null) {
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
    StingerSoftAggrid.Renderer.ProgressBarRenderer.prototype.getGui = function () {
        return this.eGui;
    };

    /**
     *
     * @param params
     */
    StingerSoftAggrid.Renderer.ProgressBarRenderer.prototype.refresh = function (params) {
        this.init(params);
    };

    /**
     *
     * @returns StingerSoftAggrid.Renderer.UserRenderer
     */
    StingerSoftAggrid.Renderer.UserRenderer = function (rendererParams) {
        this.eGui = document.createElement('abbr');
    };

    /**
     *
     * @param params
     */
    StingerSoftAggrid.Renderer.UserRenderer.prototype.init = function (params) {
        if (params.value !== "" && params.value !== undefined && params.value !== null) {
            try {
                var userUrl = Routing.generate('pec_social_popover', {
                    'user': params.value.id,
                    'username': params.value.username
                });
            } catch (err) {
            }

            //
            var $abbr = jQuery(this.eGui);
            $abbr.attr('title', params.value.username + " - " + params.value.email);
            $abbr.attr('data-toggle', 'tooltip');
            $abbr.attr('data-container', 'body');
            $abbr.attr('data-placement', 'left');
            $abbr.attr('data-id', params.value.id);
            $abbr.attr('data-username', params.value.username);
            if (typeof userUrl !== "undefined") {
                this.eGui.className = "platform-user-name popover-ajax hover-stay";
                $abbr.attr('data-html', true);
                $abbr.attr('data-trigger', "hover");
                $abbr.attr('data-popover-url', userUrl);
                $abbr.attr('data-template', '<div class="popover user" role="tooltip" style="max-width: 350px; width: 350px;"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>');
            } else {
                this.eGui.className = "platform-user-name";
            }
            if ("firstname" in params.value) {
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
    StingerSoftAggrid.Renderer.UserRenderer.prototype.getGui = function () {
        return this.eGui;
    };

    /**
     *
     * @param params
     */
    StingerSoftAggrid.Renderer.UserRenderer.prototype.refresh = function (params) {
        this.init(params);
    };


    /**
     *
     * @returns StingerSoftAggrid.Renderer.UserFilterRenderer
     */
    StingerSoftAggrid.Renderer.UserFilterRenderer = function (rendererParams) {
        this.eGui = document.createElement('span');
    };

    /**
     *
     * @param params
     */
    StingerSoftAggrid.Renderer.UserFilterRenderer.prototype.init = function (params) {
        if (params.value !== "" && params.value !== undefined && params.value !== null) {
            var values = params.value.split("|");
            if (values.length > 1) {
                this.eGui.innerHTML = (values[0] && values[0] != "null" ? values[0] : "") + " " + (values[1] && values[1] != "null" ? values[1] : "");
            }
        }
    };

    /**
     *
     * @returns {HTMLElement | *}
     */
    StingerSoftAggrid.Renderer.UserFilterRenderer.prototype.getGui = function () {
        return this.eGui;
    };

    /**
     *
     * @param params
     */
    StingerSoftAggrid.Renderer.UserFilterRenderer.prototype.refresh = function (params) {
        this.init(params);
    };

}));