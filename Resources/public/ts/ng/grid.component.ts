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
    ViewContainerRef, ViewChildren, Directive, HostListener
} from '@angular/core';
import type { BazingaTranslator } from 'bazinga-translator';
import {Router} from '@angular/router';

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
        @Inject(ComponentFactoryResolver) private componentFactoryResolver: ComponentFactoryResolver,
        @Inject(Router) private router: Router
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

        if (this.configuration.stinger.attr) {
            element.setAttribute('style', this.configuration.stinger.attr.style);
            element.classList.add(this.configuration.stinger.attr.class);
            element.id = this.configuration.stinger.attr.id;
        }
    }

    @HostListener('document:click', ['$event'])
    public handleClick(event: Event): void {
        if (event.target instanceof HTMLAnchorElement) {
            const element = event.target as HTMLAnchorElement;
            if (element.className === 'routerlink' && element?.hasAttribute('nghref')) {
                event.preventDefault();
                const route = element?.getAttribute('nghref');
                if (route) {
                    event.preventDefault();
                    this.router.navigate([`/${route}`]);
                }
            }
        }
    }
}