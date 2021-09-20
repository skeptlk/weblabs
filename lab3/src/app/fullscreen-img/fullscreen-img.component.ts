import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'fullscreen-img',
  templateUrl: './fullscreen-img.component.html',
  styleUrls: ['./fullscreen-img.component.scss']
})
export class FullscreenImgComponent {

  constructor() { }

  @Input() src: string;
  @Input() alt: string;
  @Output() onClose: EventEmitter<void> = new EventEmitter();

  zoomValue: number = 1.0;

  zoom($event: WheelEvent) {
    $event.preventDefault();

    if ($event.deltaY < 0) {
      this.zoomValue += 0.05;
    } else {
      this.zoomValue = Math.max(1, this.zoomValue - 0.05);
    }
  }

}
