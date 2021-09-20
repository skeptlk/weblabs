import { Component } from '@angular/core';
import { AngularFirestore, AngularFirestoreCollection } from '@angular/fire/firestore';

export interface Aircraft { 
    name: string, 
    type: string,
    cost: number, 
    engine: string, 
    engineCount: number, 
    manufacturer: string,
    image: string,
    status: string,
    firstFlight: number
};

export interface Option {
    name: string, 
    isSelected: boolean,
    isDisabled: boolean
}

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss']
})
export class AppComponent {
    private aircraftCollection: AngularFirestoreCollection<Aircraft>;

    isLoading: boolean;
    aircrafts: Aircraft[] = [];
    filteredAircrafts: Aircraft[] = [];
    fullscreen: Aircraft;

    filters: any = {
        manufacturer: {},
        engineCount: {}, 
        type: {}, 
        status: {},
        cost: [0, 300000000],
        firstFlight: [1930, 2010]
    };
    optionsFliters = ["manufacturer", "engineCount", "type", "status"];
    rangeFilters = ["firstFlight"]
    serverFilters = ["cost"];
    rangeFiltersBoundaries = {
        cost: [0, 300000000], 
        firstFlight: [1930, 2010]
    }

    constructor(private firestore: AngularFirestore) {
        this.aircraftCollection = this.firestore.collection<Aircraft>('aircrafts');
        this.getAircrafts(true);
    }

    getAircrafts(isInitial: boolean = false): void {
        let query = this.aircraftCollection.ref.orderBy("cost");
        this.isLoading = true;

        let costRange = this.filters.cost;
        
        if (costRange[0] >= 0 && costRange[1] >= 0 
            && costRange[0] < costRange[1]) {            
            query = query.where("cost", ">=", costRange[0]);
            query = query.where("cost", "<=", costRange[1]);
        }

        this.firestore
            .collection<Aircraft>('aircrafts', () => query)
            .valueChanges().subscribe(data => {
                this.isLoading = false;
                if (isInitial) {
                    for (let filter of this.optionsFliters)
                        this.setFilterValues(filter, data);
                }
                this.aircrafts = data;
                
                this.filteredAircrafts = this.applyFilters();
                this.filterChanged();
            });
    }

    // returns list of aircrafts when all filters is applied
    applyFilters(): Aircraft[] {
        let items = this.aircrafts;

        for (let prop of this.optionsFliters) {
            if (!this.isFilterEmpty(prop)) {
                let filter = this.filters[prop];
                items = items.filter(item => filter[item[prop]].isSelected);
            }
        }
        for (let prop of this.rangeFilters) {
            let range = this.filters[prop];
            if (range[0] >= 0 && range[1] >= 0 && range[0] < range[1]) {
                items = items.filter(item => item[prop] >= range[0] && item[prop] <= range[1])
            }
        }
        return items;
    }

    // returns list of aircrafts when all filters but one is applied
    applyFiltersExceptOne(filterToExclude: string): Aircraft[] {
        let items = this.aircrafts;

        for (let prop of this.optionsFliters) {
            if (prop !== filterToExclude && !this.isFilterEmpty(prop)) {
                let filter = this.filters[prop];
                items = items.filter(item => filter[item[prop]].isSelected);
            }
        }
        for (let prop of this.rangeFilters) {
            let range = this.filters[prop];
            if (range[0] >= 0 && range[1] >= 0 && range[0] < range[1]) {
                items = items.filter(item => item[prop] >= range[0] && item[prop] <= range[1])
            }
        }
        return items;
    }

    filterChanged(name: string = "") {
        if (name && this.serverFilters.includes(name)) {
            this.getAircrafts();
        } else {
            this.filteredAircrafts = this.applyFilters();
    
            for (let filter of this.optionsFliters) {
                this.setDisabledFilterOptions(filter, this.applyFiltersExceptOne(filter));
            }
        }
    }
    
    setFilterValues(filter: string, items: Aircraft[]) {
        let filterValues = items.map(item => item[filter]);
        filterValues = [... new Set(filterValues)];
        
        let result = {};
        for (let value of filterValues) {
            result[value] = { name: value, isSelected: false, isDisabled: false };
        }
        this.filters[filter] = result;
    }


    setDisabledFilterOptions(filter: string, items: Aircraft[]) {
        // disable all 
        for (let option of Object.values(this.filters[filter]))
            option["isDisabled"] = true;

        let values = this.filters[filter];
        for (let item of items) {
            values[item[filter]].isDisabled = false;
        }
    }

    isFilterEmpty(filter: string) {
        for (let item of Object.values(this.filters[filter]))
            if (item["isSelected"]) return false;
        return true;
    }
}
