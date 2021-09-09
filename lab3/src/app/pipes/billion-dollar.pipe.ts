import { Pipe, PipeTransform } from '@angular/core';

@Pipe({name: 'toBillion'})
export class BillionDollarPipe implements PipeTransform {
  transform(cost: number): string {
    return cost <= 0 ?
      "0$" : (cost / 1000000).toFixed(0) + " млн $";
  }
}