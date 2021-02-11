import {
    Component,
    Inject,
    Input,
    OnInit,
    AfterViewInit,
    ViewChild,
    ViewEncapsulation,
    ComponentFactory,
    ComponentRef,
    ComponentFactoryResolver,
    ViewContainerRef, ViewChildren
} from '@angular/core';
import type { BazingaTranslator } from 'bazingajstranslation/js/translator.min.js';
declare var Translator: BazingaTranslator;
import {HttpClient} from '@angular/common/http';
import {AgGridAngular} from 'ag-grid-angular';
import("ag-grid-enterprise");
import {LicenseManager} from "ag-grid-enterprise";
import {StingerSoftAggrid} from "../StingerSoftAggrid";
import {GridConfiguration} from "../GridConfiguration";
LicenseManager.setLicenseKey("your license key");

@Component({
    selector: 'stinger-grid',
    templateUrl: './grid.component.html',
})
export class GridComponent implements OnInit, AfterViewInit {
    agGrid: AgGridAngular;

    @ViewChild('gridContainer', {read: ViewContainerRef}) entry: ViewContainerRef;

    @Input() src: string;

    configuration: any;

    stingerAggrid: StingerSoftAggrid;

    constructor(
        @Inject(HttpClient) private http: HttpClient,
        @Inject(ComponentFactoryResolver) private componentFactoryResolver: ComponentFactoryResolver
    ) {
    }

    fetchColumnDefs(url: string) {
        this.http.post<GridConfiguration>(url, {
            'agGrid': {
                'gridId': 1
            }
        }).subscribe((p) => {
            this.configuration = p;
            this.processConfiguration();
            this.createAggrid();
        });
    }

    ngAfterViewInit() {
        this.fetchColumnDefs(this.src);
    }

    ngOnInit(): void {

    }

    processConfiguration() {
        StingerSoftAggrid.processJsonConfiguration(this.configuration);
        console.log(this.configuration);
    }

    createAggrid(): void {
        const componentFactory = this.componentFactoryResolver.resolveComponentFactory(AgGridAngular);
        const componentRef = this.entry.createComponent(componentFactory);
        this.agGrid = componentRef.instance;
        componentRef.instance.gridOptions = this.configuration.aggrid;

        componentRef.instance.gridReady.subscribe((r) => {
            this.stingerAggrid = new StingerSoftAggrid(element, this.agGrid.api, this.agGrid.columnApi);
            this.stingerAggrid.init(this.configuration);
        });

        // getting the component's HTML
        let element: HTMLElement = <HTMLElement>componentRef.location.nativeElement;

        if(this.configuration.stinger.attr) {
            element.setAttribute('style', this.configuration.stinger.attr.style);
            element.classList.add(this.configuration.stinger.attr.class);
            element.id = this.configuration.stinger.attr.id;
        }
    }


}