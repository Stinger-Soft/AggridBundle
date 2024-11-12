import {ModuleRegistry} from "@ag-grid-community/core";
import {ClientSideRowModelModule} from "@ag-grid-community/client-side-row-model";
import {RowGroupingModule} from "@ag-grid-enterprise/row-grouping";
import {StatusBarModule} from "@ag-grid-enterprise/status-bar";
import {SideBarModule} from "@ag-grid-enterprise/side-bar";
import {SetFilterModule} from "@ag-grid-enterprise/set-filter";
import { ColumnsToolPanelModule } from "@ag-grid-enterprise/column-tool-panel";
import { MenuModule } from "@ag-grid-enterprise/menu";
import { FiltersToolPanelModule } from '@ag-grid-enterprise/filter-tool-panel';

ModuleRegistry.registerModules([ClientSideRowModelModule, RowGroupingModule, StatusBarModule, SideBarModule, SetFilterModule, ColumnsToolPanelModule, MenuModule, FiltersToolPanelModule]);
export {GridComponent} from "./GridComponentCommunity";