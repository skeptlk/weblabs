import { BrowserModule } from '@angular/platform-browser';
import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { AppComponent } from './app.component';
import { AngularFireModule } from '@angular/fire';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AngularFirestoreModule } from '@angular/fire/firestore';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { CardModule } from 'primeng/card';
import { MultiSelectModule } from 'primeng/multiselect';
import { ProgressSpinnerModule } from 'primeng/progressspinner';
import { SliderModule } from 'primeng/slider';
import { CheckboxModule } from 'primeng/checkbox';

const config = {
    apiKey: "AIzaSyDFeh14LEhUzZtXpmLmylvZCf1050SzSZ0",
    authDomain: "smart-filter-272.firebaseapp.com",
    databaseURL: "https://smart-filter-272.firebaseio.com",
    projectId: "smart-filter-272",
    storageBucket: "smart-filter-272.appspot.com",
    messagingSenderId: "764363064869",
    appId: "1:764363064869:web:9fe71a859dfd3a609861cc"
};


@NgModule({
    declarations: [
        AppComponent
    ],
    schemas: [CUSTOM_ELEMENTS_SCHEMA],
    imports: [
        BrowserModule,
        CommonModule,
        FormsModule,
        AngularFireModule.initializeApp(config),
        AngularFirestoreModule,
        CardModule,
        MultiSelectModule,
        BrowserAnimationsModule,
        ProgressSpinnerModule,
        SliderModule,
        CheckboxModule
    ],
    providers: [],
    bootstrap: [AppComponent]
})
export class AppModule { }
