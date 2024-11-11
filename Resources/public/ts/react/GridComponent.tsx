import {ModuleRegistry, GridReadyEvent} from "@ag-grid-community/core";
import {AgGridReact} from "@ag-grid-community/react";
import {ClientSideRowModelModule} from "@ag-grid-community/client-side-row-model";
import {StatusBarModule} from '@ag-grid-enterprise/status-bar';
import {SideBarModule} from '@ag-grid-enterprise/side-bar';
import {SetFilterModule} from '@ag-grid-enterprise/set-filter';
import {RowGroupingModule} from "@ag-grid-enterprise/row-grouping";
import React from "react";
import axios from 'axios';
import {StingerSoftAggrid} from 'stingersoftaggrid/ts/StingerSoftAggrid';
import {GridConfiguration} from 'stingersoftaggrid/ts/GridConfiguration';
import type {BazingaTranslator} from 'bazinga-translator';
import "./GridComponent.scss";
import {NavigateFunction} from "react-router-dom";

ModuleRegistry.registerModules([ClientSideRowModelModule, RowGroupingModule, StatusBarModule, SideBarModule, SetFilterModule]);
declare var Translator: BazingaTranslator;


interface IProps {
    src?: string;
    additionalAjaxRequestBody?: Object;
    translator?: BazingaTranslator;
    navigate?: NavigateFunction;
    onGridReady?: (event: GridReadyEvent) => void;
}

interface IState {
    configuration?: any;
    stingerAggrid?: StingerSoftAggrid;
    loading: boolean;
}

export class GridComponent extends React.Component<IProps, IState> {

    gridRef: React.RefObject<AgGridReact>;
    gridContainer: React.RefObject<HTMLDivElement>;

    translator: BazingaTranslator;
    navigate?: NavigateFunction;

    abortController: AbortController | null;

    additionalAjaxRequestBody: Object | null;

    constructor(props: IProps) {
        super(props);
        this.translator = props.translator || global.Translator;
        this.navigate = props.navigate;
        this.gridRef = React.createRef<AgGridReact>();
        this.gridContainer = React.createRef<HTMLDivElement>();
        this.state = {configuration: null, stingerAggrid: null, loading: true}
        this.abortController = null;
        this.additionalAjaxRequestBody = props.additionalAjaxRequestBody;

        this.gridReadyListener = this.gridReadyListener.bind(this);
        this.handleClick = this.handleClick.bind(this);
    }


    fetchColumnDefs(url: string) {
        this.abortController = new AbortController();
        axios.post<GridConfiguration>(url, {
            'agGrid': {
                'gridId': 1,
            },
            ...this.additionalAjaxRequestBody
        }, {signal: this.abortController?.signal}).then((p) => {
            let configuration = p.data;
            configuration = this.processConfiguration(configuration);
            this.setState({configuration: configuration, loading: false});
        });
    }

    componentDidMount() {
        this.fetchColumnDefs(this.props.src);
    }

    componentWillUnmount() {
        this.getStingerApi()?.saveState();
        this.abortController?.abort();
    }

    componentDidUpdate(prevProps: Readonly<IProps>, prevState: Readonly<IState>, snapshot?: any) {
        if (prevProps.src !== this.props.src) {
            this.getStingerApi()?.saveState();
            this.abortController?.abort();
            this.setState({configuration: null, loading: true});
            this.fetchColumnDefs(this.props.src);
        }
    }

    gridReadyListener(event: GridReadyEvent) {
        const agGrid = this.gridRef.current;
        // @ts-ignore
        const stingerAggrid = new StingerSoftAggrid(this.gridContainer.current, agGrid.api, agGrid.columnApi);
        stingerAggrid.init(this.state.configuration);
        this.setState({stingerAggrid: stingerAggrid}, () => {
            if (this.props.onGridReady) {
                this.props.onGridReady(event)
            }
        });
    }

    getStingerApi(): StingerSoftAggrid {
        return this.state.stingerAggrid;
    }


    processConfiguration(configuration) {
        if (this.additionalAjaxRequestBody) {
            configuration.stinger.additionalAjaxRequestBody = this.additionalAjaxRequestBody;
        }
        StingerSoftAggrid.processJsonConfiguration(configuration);
        return configuration;
    }

    /**
     * @function parseStyles
     * Parses a string of inline styles into a javascript object with casing for react
     *
     * @param {string} styles
     * @returns {Object}
     */
    parseStyles(styles: string): any {
        return styles
            .split(';')
            .filter(style => style.split(':')[0] && style.split(':')[1])
            .map(style => [
                style.split(':')[0].trim().replace(/-./g, c => c.substr(1).toUpperCase()),
                style.split(':')[1].trim()
            ])
            .reduce((styleObj, style) => ({
                ...styleObj,
                [style[0]]: style[1],
            }), {});
    }

    handleClick(event: Event): void {
        if (event.target instanceof HTMLAnchorElement) {
            const element = event.target as HTMLAnchorElement;
            if (element.className === 'routerlink' && element?.hasAttribute('reacthref') && this.navigate) {
                event.preventDefault();
                const route = element?.getAttribute('reacthref');
                if (route) {
                    event.preventDefault();
                    this.navigate(route);
                }
            }
        }
    }

    render() {
        if (this.state.loading) {
            return "";
        }

        const configuration = this.state.configuration;
        const stingerAggrid = this.state.stingerAggrid;
        if (configuration.stinger.attr && configuration.stinger.attr.hasOwnProperty('class')) {
            configuration.stinger.attr['className'] = configuration.stinger.attr['class'];
            delete configuration.stinger.attr['class'];
        }
        if (configuration.stinger.attr && configuration.stinger.attr.hasOwnProperty('style') && !(typeof configuration.stinger.attr['style'] === 'object')) {
            configuration.stinger.attr['style'] = this.parseStyles(configuration.stinger.attr['style']);
        }

        return (<>
                <div>
                    <div className="mb-1 d-flex aggrid-topbar">
                        <div className="flex-row d-flex ">
                            {configuration.stinger.searchEnabled &&
                                <input type="text"
                                       className="form-control input-small flex-column-auto d-flex aggrid-quick-search"
                                       placeholder={Translator.trans('stingersoft_aggrid.searchOoo', {}, 'StingerSoftAggridBundle')}
                                       onChange={(e) => stingerAggrid?.quickFilter(e.target.value)}/>
                            }

                            <div className="flex-column-fluid d-flex aggrid-actions">
                                {configuration?.aggrid.pagination && configuration?.stinger.paginationDropDown !== null && configuration?.stinger.paginationDropDown.length > 0 &&
                                    <div className="form-group row aggrid-entries-per-page">
                                        <label
                                            className="col-6 col-form-label text-end">{Translator.trans('stingersoft_aggrid.pagination.entries_per_page', {}, 'StingerSoftAggridBundle')}</label>
                                        <div className="col-6">
                                            <select className="form-select"
                                                    onChange={(e) => stingerAggrid?.setPaginationPageSize(e.target.value)}>
                                                {configuration?.stinger.paginationDropDown.map(entry =>
                                                    <option
                                                        selected={entry == configuration?.aggrid.paginationPageSize ? true : null}
                                                        value={entry}> {entry}</option>
                                                )}
                                                {configuration?.stinger.paginationDropDown.indexOf(configuration?.aggrid.paginationPageSize) < 0 &&
                                                    <option selected={true}
                                                            value={configuration?.aggrid.paginationPageSize}> {configuration?.aggrid.paginationPageSize}</option>}
                                                <option
                                                    value="999999999">{Translator.trans('stingersoft_aggrid.selectAll', {}, 'StingerSoftAggridBundle')}</option>
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
                                               title={Translator.trans('stingersoft_aggrid.autosizeAllColumns', {}, 'StingerSoftAggridBundle')}>
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
                                               title={Translator.trans('stingersoft_aggrid.refresh', {}, 'StingerSoftAggridBundle')}>
                                                <i className="far fa-fw fa-sync"></i>
                                            </a>
                                        }
                                        {configuration?.stinger.clearFilterButton &&
                                            <a href="" className="btn btn-light btn-icon aggrid-clear"
                                               data-toggle="tooltip"
                                               onClick={(e) => {
                                                   e.preventDefault();
                                                   stingerAggrid?.resetFilter();
                                               }}
                                               title={Translator.trans('stingersoft_aggrid.clearFilter', {}, 'StingerSoftAggridBundle')}>
                                                <i className="far fa-fw fa-trash"></i>
                                            </a>
                                        }
                                    </div>
                                }
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
                            // modules={AllCommunityModules}
                            ref={this.gridRef}
                            //rowData={[]}
                            columnDefs={this.state.configuration.aggrid.columnDefs}
                            gridOptions={this.state.configuration.aggrid}
                        ></AgGridReact>
                    </div>
                </div>
            </>
        );
    }
}
