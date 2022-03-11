import {CUSTOM_ELEMENTS_SCHEMA, NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {AgGridAngular, AgGridModule} from "ag-grid-angular";
import {GridComponent} from "./grid.component";
import {TranslationPipe} from "./pipe/translation.pipe";

@NgModule({
    declarations: [
        TranslationPipe,
        GridComponent
    ],
    exports: [
        TranslationPipe,
        GridComponent
    ],
    imports: [
        AgGridModule.withComponents([]),
        CommonModule
    ],
    bootstrap: [
        GridComponent
    ],
    providers: [],
    entryComponents: [AgGridAngular],
    schemas: [
        CUSTOM_ELEMENTS_SCHEMA
    ]
})
export class StingetSoftAggridModule {
}
