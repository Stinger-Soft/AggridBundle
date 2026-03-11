import {AggridConfiguration} from "./AggridConfiguration";
import {StingerConfiguration} from "./StingerConfiguration";
import {GridOptions} from "@ag-grid-community/core";

export class GridConfiguration {
    aggrid: GridOptions
    gridId?: string
    stinger: StingerConfiguration
}