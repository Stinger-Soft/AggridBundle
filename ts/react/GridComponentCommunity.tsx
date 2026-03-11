import React, {createRef, type FunctionComponent} from "react";
import {ModuleRegistry, type GridReadyEvent} from "@ag-grid-community/core";
import {AgGridReact} from "@ag-grid-community/react";
import {ClientSideRowModelModule} from "@ag-grid-community/client-side-row-model";
import axios, {type AxiosResponse} from 'axios';
import {StingerSoftAggrid} from '../utils/StingerSoftAggrid';
import type {GridConfiguration} from '../utils/GridConfiguration';
import type {BazingaTranslator} from 'bazinga-translator';
import "./GridComponent.scss";
import type {NavigateFunction} from "react-router-dom";
import {LicenseManager} from "@ag-grid-enterprise/core";

ModuleRegistry.register(ClientSideRowModelModule);

interface IProps {
    src?: string;
    additionalAjaxRequestBody?: object;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    additionalGridAction?: Array<{component: FunctionComponent, props: any}>
    translator?: BazingaTranslator;
    navigate?: NavigateFunction;
    onGridReady?: (event: GridReadyEvent) => void;
}

interface IState {
     
    configuration: GridConfiguration | null;
    stingerAggrid: StingerSoftAggrid | null;
    loading: boolean;
}

export class GridComponent extends React.Component<IProps, IState> {

    gridRef: React.RefObject<AgGridReact | null>;
    gridContainer: React.RefObject<HTMLDivElement | null>;

    translator: BazingaTranslator;
    navigate?: NavigateFunction;

    abortController: AbortController | null;

    additionalAjaxRequestBody: object | undefined;

    constructor(props: IProps) {
        super(props);
         
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        this.translator = props.translator ?? (globalThis as any).Translator;
        this.navigate = props.navigate;
        this.gridRef = createRef<AgGridReact>();
        this.gridContainer = createRef<HTMLDivElement>();
        const initialState: IState = {configuration: null, stingerAggrid: null, loading: true};
        this.state = initialState;
        this.abortController = null;
        this.additionalAjaxRequestBody = props.additionalAjaxRequestBody;

        this.gridReadyListener = this.gridReadyListener.bind(this);
        this.handleClick = this.handleClick.bind(this);
        this.handleWindowBeforeUnload = this.handleWindowBeforeUnload.bind(this);
    }


    fetchColumnDefs(url: string): void {
        this.abortController = new AbortController();
        axios.post<GridConfiguration, AxiosResponse<GridConfiguration>>(url, {
            'agGrid': {
                'gridId': 1,
            },
            ...this.additionalAjaxRequestBody
        }, {signal: this.abortController.signal}).then((p: AxiosResponse<GridConfiguration>) => {
            let configuration = p.data;
            configuration = this.processConfiguration(configuration);
            this.setState({configuration, loading: false, stingerAggrid: null});
        }).catch((err: unknown) => {
            console.warn('Failed to fetch column defs', err);
        });
    }

    componentDidMount(): void {
        window.addEventListener("beforeunload", this.handleWindowBeforeUnload);
        this.fetchColumnDefs(this.props.src!);
    }

    componentWillUnmount(): void {
        window.removeEventListener("beforeunload", this.handleWindowBeforeUnload);
        this.getStingerApi()?.saveState();
        this.abortController?.abort();
    }

    handleWindowBeforeUnload(_ev: BeforeUnloadEvent): void  {
        this.getStingerApi()?.saveState();
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    componentDidUpdate(prevProps: Readonly<IProps>, _prevState: Readonly<IState>, _snapshot?: any): void {
        this.getStingerApi()?.saveState();
        if (prevProps.src !== this.props.src) {
            this.getStingerApi()?.saveState();
            this.abortController?.abort();
            this.setState({configuration: null, loading: true, stingerAggrid: null});
            this.fetchColumnDefs(this.props.src!);
        }
    }

    gridReadyListener(event: GridReadyEvent): void {
        const agGrid = this.gridRef.current!;
        const stingerAggrid = new StingerSoftAggrid(this.gridContainer.current as HTMLElement, agGrid.api);
        if(!this.state.configuration) {
            throw Error("No configuration set!");
        }
        stingerAggrid.init(this.state.configuration);
        this.setState({...this.state, stingerAggrid}, () => {
            if (this.props.onGridReady) {
                this.props.onGridReady(event)
            }
        });
    }

    getStingerApi(): StingerSoftAggrid | null | undefined {
        return this.state.stingerAggrid;
    }

    processConfiguration(configuration: GridConfiguration): GridConfiguration {
        if (this.additionalAjaxRequestBody) {
            configuration.stinger.additionalAjaxRequestBody = this.additionalAjaxRequestBody;
        }
        if (typeof configuration.stinger.enterpriseLicense === "string" && configuration.stinger.enterpriseLicense.length > 0) {
            LicenseManager.setLicenseKey(configuration.stinger.enterpriseLicense)
        }
        StingerSoftAggrid.processJsonConfiguration(configuration, this.translator);
        return configuration;
    }

    /**
     * @function parseStyles
     * Parses a string of inline styles into a javascript object with casing for react
     */
    static parseStyles(styles: string): Record<string, string> {
        return styles
            .split(';')
            .filter(style => style.split(':')[0] && style.split(':')[1])
            .map(style => [
                // eslint-disable-next-line require-unicode-regexp -- v flag requires ES2024+
                style.split(':')[0].trim().replace(/-./g, c => c.substring(1).toUpperCase()),
                style.split(':')[1].trim()
            ])
            .reduce<Record<string, string>>((styleObj, style) => ({
                ...styleObj,
                [style[0]]: style[1],
            }), {});
    }

    handleClick(event: Event): void {
        if (event.target instanceof HTMLAnchorElement) {
            const element = event.target;
            if (element.className === 'routerlink' && element.hasAttribute('reacthref') && this.navigate) {
                event.preventDefault();
                const route = element.getAttribute('reacthref');
                if (route) {
                    event.preventDefault();
                    this.navigate(route);
                }
            }
        }
    }

    render(): React.ReactNode {

        if (this.state.loading) {
            return "";
        }
        const {configuration, stingerAggrid} = this.state;
        if (!configuration?.stinger?.attr || !configuration.aggrid) {
            return "";
        }

        if (configuration.stinger.attr.hasOwnProperty('class')) {
            configuration.stinger.attr.className = configuration.stinger.attr.class;
            delete configuration.stinger.attr.class;
        }
        if (configuration.stinger.attr?.hasOwnProperty('style') && !(typeof configuration.stinger.attr.style === 'object')) {
            configuration.stinger.attr.style = GridComponent.parseStyles(configuration.stinger.attr.style);
        }

        return (<>
                <div style={{height: "inherit"}}>
                    <div className="mb-1 d-flex aggrid-topbar">
                        <div className="flex-row d-flex ">
                            {configuration.stinger.searchEnabled &&
                                <input type="text"
                                       id="aggrid-quick-search"
                                       className="form-control input-small flex-column-auto d-flex aggrid-quick-search"
                                       placeholder={this.translator.trans('stingersoft_aggrid.searchOoo', {}, 'StingerSoftAggridBundle')}
                                       onChange={(e) => stingerAggrid?.quickFilter(e.target.value)}/>
                            }

                            <div className="flex-column-fluid d-flex aggrid-actions">
                                {configuration?.aggrid.pagination && configuration?.stinger.paginationDropDown !== null && configuration?.stinger.paginationDropDown.length > 0 &&
                                    <div className="form-group row aggrid-entries-per-page">
                                        <label
                                            className="col-6 col-form-label text-end">{this.translator.trans('stingersoft_aggrid.pagination.entries_per_page', {}, 'StingerSoftAggridBundle')}</label>
                                        <div className="col-6">
                                            <select className="form-select"
                                                    onChange={(e) => stingerAggrid?.setPaginationPageSize(e.target.value)}>
                                                {/* eslint-disable-next-line @typescript-eslint/no-explicit-any */}
                                                {configuration?.stinger.paginationDropDown.map((entry: any) =>
                                                    <option
                                                        // eslint-disable-next-line eqeqeq -- loose comparison for numeric/string paginationPageSize
                                                        selected={entry == configuration?.aggrid.paginationPageSize ? true : undefined}
                                                        value={entry}
                                                        key={String(entry)}> {entry}</option>
                                                )}
                                                {configuration?.stinger.paginationDropDown.indexOf(configuration?.aggrid.paginationPageSize) < 0 &&
                                                    <option selected={true}
                                                            value={configuration?.aggrid.paginationPageSize}> {configuration?.aggrid.paginationPageSize}</option>}
                                                <option
                                                    value="999999999">{this.translator.trans('stingersoft_aggrid.selectAll', {}, 'StingerSoftAggridBundle')}</option>
                                            </select>
                                        </div>
                                    </div>
                                }
                                {(configuration?.stinger.reloadButton || configuration?.stinger.clearFilterButton || configuration?.stinger.autosizeColumnsButton) &&
                                    <div className="aggrid-action-buttons">
                                        {configuration?.stinger.autosizeColumnsButton &&
                                            <a href="" className="btn btn-light btn-icon aggrid-autosize"
                                               data-toggle="tooltip"
                                               onClick={(e) => {
                                                   e.preventDefault();
                                                   stingerAggrid?.autoSizeColumns()
                                               }}
                                               title={this.translator.trans('stingersoft_aggrid.autosizeAllColumns', {}, 'StingerSoftAggridBundle')}>
                                                <i className="far fa-fw fa-text-width"></i>
                                            </a>
                                        }
                                        {configuration?.stinger.reloadButton &&
                                            <a href="" className="btn btn-light btn-icon aggrid-reload"
                                               data-toggle="tooltip"
                                               onClick={(e) => {
                                                   e.preventDefault();
                                                   stingerAggrid?.reload();
                                               }}
                                               title={this.translator.trans('stingersoft_aggrid.refresh', {}, 'StingerSoftAggridBundle')}>
                                                <i className="far fa-fw fa-sync"></i>
                                            </a>
                                        }
                                        {configuration?.stinger.clearFilterButton &&
                                            <a href="" className="btn btn-light btn-icon aggrid-clear"
                                               data-toggle="tooltip"
                                               onClick={(e) => {
                                                   const input = document.getElementById("aggrid-quick-search") as HTMLInputElement;
                                                   if (input && input.value.length > 0) {
                                                       input.value = "";
                                                   }
                                                   e.preventDefault();
                                                   stingerAggrid?.resetFilter();
                                               }}
                                               title={this.translator.trans('stingersoft_aggrid.clearFilter', {}, 'StingerSoftAggridBundle')}>
                                                <i className="far fa-fw fa-trash"></i>
                                            </a>
                                        }
                                    </div>
                                }
                                {this.props.additionalGridAction && Array.from(this.props.additionalGridAction).map((actionItem, key) => (
                                        <div className="aggrid-action-buttons" key={`additional_actiom_item_${String(key)}`}>
                                            {React.createElement(actionItem.component, actionItem.props)}
                                        </div>
                                    ))}
                            </div>
                        </div>
                    </div>
                    <div
                        ref={this.gridContainer}
                        onClick={this.handleClick}
                        {...configuration.stinger.attr}
                    >
                        <AgGridReact
                            onGridReady={this.gridReadyListener}
                            ref={this.gridRef}
                            columnDefs={configuration.aggrid.columnDefs}
                            gridOptions={configuration.aggrid}
                        ></AgGridReact>
                    </div>
                </div>
            </>
        );
    }
}
