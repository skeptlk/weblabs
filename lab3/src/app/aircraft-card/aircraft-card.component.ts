import { Component, Input, OnInit } from '@angular/core';
import { Aircraft } from '../app.component';

@Component({
  selector: 'aircraft-card',
  templateUrl: './aircraft-card.component.html',
  styleUrls: ['./aircraft-card.component.scss']
})
export class AircraftCardComponent implements OnInit {

  @Input() data: Aircraft;

  constructor() { }



  ngOnInit(): void {
  }

}
