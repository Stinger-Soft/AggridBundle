import {ColDef, GridApi} from '@ag-grid-community/core';

export class AggridConfiguration {
    api?: GridApi
    columnDefs: ColDef[]
    components?: any
    statusBar?: any
    sideBar?: any
    rowStyle: string
    rowSelection: any
    getRowNodeId: any
    rowHeight: any
    getRowStyle: any
    rowClass: any
    getRowClass: any
    rowClassRules: any
    icons: any
    suppressCsvExport: boolean
    pagination: any
}