import { Component, EventEmitter, Input, Output } from '@angular/core';
import { Aircraft } from '../app.component';

@Component({
  selector: 'aircraft-card',
  templateUrl: './aircraft-card.component.html',
  styleUrls: ['./aircraft-card.component.scss']
})
export class AircraftCardComponent {

  @Input() data: Aircraft;
  @Output() onHeaderClick: EventEmitter<void> = new EventEmitter();

  constructor() { }

  test() {
    console.log(123);
    
  }

}
