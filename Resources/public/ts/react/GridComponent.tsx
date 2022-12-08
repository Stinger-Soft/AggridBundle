import {AllCommunityModules} from "@ag-grid-community/all-modules";
import {AgGridReact} from "@ag-grid-community/react";
import React from "react";
import axios from 'axios';
import {StingerSoftAggrid} from 'stingersoftaggrid/ts/StingerSoftAggrid';
import {GridConfiguration} from 'stingersoftaggrid/ts/GridConfiguration';
import type {BazingaTranslator} from 'bazingajstranslation/js/translator.min.js';
import "./GridComponent.scss";

declare var Translator: BazingaTranslator;


interface IProps {
    src: string;
}

interface IState {
    configuration?: any;
    stingerAggrid?: StingerSoftAggrid;
    loading: boolean;
}

export class GridComponent extends React.Component<IProps, IState> {

    gridRef: React.RefObject<AgGridReact>;
    gridContainer: React.RefObject<HTMLDivElement>;

    constructor(props) {
        super(props);
        this.gridRef = React.createRef<AgGridReact>();
        this.gridContainer = React.createRef<HTMLDivElement>();
        this.state = { configuration: null, stingerAggrid: null, loading: true }
        this.fetchColumnDefs(props.src);
        this.gridReadyListener = this.gridReadyListener.bind(this);
    }


    fetchColumnDefs(url: string) {
        axios.post<GridConfiguration>(url, {
            'agGrid': {
                'gridId': 1
            }
        }).then((p) => {
            console.log(p);
            let configuration = p.data;
            configuration = this.processConfiguration(configuration);
            this.setState({ configuration: configuration, loading: false  });
        });
    }

    componentDidMount() {
    }

    gridReadyListener() {
        const agGrid = this.gridRef.current;
        // @ts-ignore
        const stingerAggrid = new StingerSoftAggrid(this.gridContainer.current, agGrid.api, agGrid.columnApi);
        stingerAggrid.init(this.state.configuration);
        this.setState({ stingerAggrid: stingerAggrid});
    }

    processConfiguration(configuration) {
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

    render() {
        if(this.state.loading) {
            return "";
        }

        const configuration = this.state.configuration;
        const stingerAggrid = this.state.stingerAggrid;
        if(configuration.stinger.attr && configuration.stinger.attr.hasOwnProperty('class')) {
            configuration.stinger.attr['className'] = configuration.stinger.attr['class'];
            delete configuration.stinger.attr['class'];
        }
        if(configuration.stinger.attr && configuration.stinger.attr.hasOwnProperty('style') && !(typeof configuration.stinger.attr['style'] === 'object' )) {
            configuration.stinger.attr['style'] = this.parseStyles(configuration.stinger.attr['style']);
        }

        return (<>
                <div>
                    <div className="mb-1 aggrid-topbar">
                        { configuration.stinger.searchEnabled &&
							<input type="text"
								   className="pull-left form-control input-small col-lg-3 col-xl-2  aggrid-quick-search"
								   placeholder={Translator.trans('stingersoft_aggrid.searchOoo', {}, 'StingerSoftAggridBundle')}
								   onChange={(e) => stingerAggrid?.quickFilter(e.target.value)} />
                        }

                        <div className="col-lg-9 col-xl-10 aggrid-actions row">
                            { configuration?.aggrid.pagination && configuration?.stinger.paginationDropDown !== null && configuration?.stinger.paginationDropDown.length > 0 &&
								<div className="form-group row col-lg-1 col-xl-2 aggrid-entries-per-page">
									<label className="col-6 col-form-label">{Translator.trans('stingersoft_aggrid.pagination.entries_per_page', {}, 'StingerSoftAggridBundle')}</label>
									<div className="col-6">
										<select className="form-control input-xsmall"  onChange={(e) => stingerAggrid?.setPaginationPageSize(e.target.value)}>
                                            { configuration?.stinger.paginationDropDown.map(entry =>
                                                <option selected={entry == configuration?.aggrid.paginationPageSize ? true : null} value={ entry }> { entry }</option>
                                            )}
                                            { configuration?.stinger.paginationDropDown.indexOf(configuration?.aggrid.paginationPageSize) < 0 &&  <option selected={true} value={ configuration?.aggrid.paginationPageSize }> { configuration?.aggrid.paginationPageSize }</option> }
											<option value="999999999">{ Translator.trans('stingersoft_aggrid.selectAll',{}, 'StingerSoftAggridBundle' )}</option>
										</select>
									</div>
								</div>
                            }
                            { (configuration?.stinger.reloadButton || configuration?.stinger.clearFilterButton || configuration?.stinger.autosizeColumnsButton) &&
								<div className="aggrid-action-buttons">
                                    {configuration?.stinger.autosizeColumnsButton &&
										<a href="" className="btn btn-default btn-icon aggrid-autosize"
										   data-toggle="tooltip"
										   onClick={(e) => {e.preventDefault(); stingerAggrid?.autoSizeColumns()} }
										   title={ Translator.trans('stingersoft_aggrid.autosizeAllColumns',{}, 'StingerSoftAggridBundle' )}>
											<i className="far fa-fw fa-text-width"></i>
										</a>
                                    }
                                    { configuration?.stinger.reloadButton &&
										<a href="" className="btn btn-default btn-icon aggrid-reload"
										   data-toggle="tooltip"
										   onClick={(e) => {e.preventDefault(); stingerAggrid?.reload(); } }
										   title={ Translator.trans('stingersoft_aggrid.refresh',{}, 'StingerSoftAggridBundle' )}>
											<i className="far fa-fw fa-sync"></i>
										</a>
                                    }
                                    { configuration?.stinger.clearFilterButton &&
										<a href="" className="btn btn-default btn-icon aggrid-clear"
										   data-toggle="tooltip"
										   onClick={(e) => {e.preventDefault(); stingerAggrid?.resetFilter(); } }
										   title={ Translator.trans('stingersoft_aggrid.clearFilter',{}, 'StingerSoftAggridBundle' )}>
											<i className="far fa-fw fa-trash"></i>
										</a>
                                    }
								</div>
                            }
                        </div>
                    </div>
                    <div
                        ref={this.gridContainer}
                        {...configuration.stinger.attr}
                    >
                        <AgGridReact
                            onGridReady={this.gridReadyListener}
                            modules={AllCommunityModules}
                            ref={this.gridRef}
                            rowData={[]}
                            columnDefs={this.state.configuration.aggrid.columnDefs}
                            gridOptions={this.state.configuration.aggrid}
                        ></AgGridReact>
                    </div>
                </div>
            </>
        );
    }
}
