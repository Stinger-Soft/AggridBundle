import type {StingerConfiguration} from "./StingerConfiguration";
import type {GridOptions} from "@ag-grid-community/core";

export interface GridConfiguration {
    aggrid: GridOptions
    gridId?: string
    stinger: StingerConfiguration
}