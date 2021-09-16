
interface AddRequest {
  ccypair: string;
  spot: number;
  tickTime: number;
}

interface GetRequest {
  ccypair: string;
  dateTime: number;
  /** When you handle this request don't forget to call @param cb passing the result */
  cb: (value: number) => void;
} 

interface MonitoringService {
  sendHeartBeat(): void;
}


function binarySearch<T, S>(arr: T[], val: S, getval: (b: T) => S): number {
  let start = 0;
  let end = arr.length - 1;

  while (start <= end) {
    let mid = Math.floor((start + end) / 2);

    if (val === getval(arr[mid])) {
      return mid;
    }

    // get the most recent value 
    // if between "less" and "more" or if end reached
    if (val > getval(arr[mid]) && (!arr[mid + 1] || val < getval(arr[mid + 1]))) {
    	return mid;
    }

    if (val < getval(arr[mid])) {
      end = mid - 1;
    } else {
      start = mid + 1;
    }
  }
  return -1;
}
 
interface ISpotStore {

  add(ccypair: string, spot: number, tickTime: number): void;
 
  get(ccypair: string, dateTime: number): number;

}

type Spot = {
  time: number,
  value: number,
};

/** Reactor will add "Add" request to the end of this queue */
const addRequestQueue: AddRequest[] = []; 

/** Reactor will add "Get" request to the end of this queue */
const getRequestQueue: GetRequest[] = [];

/**
 * Reactor pattern
 */
class Processor implements MonitoringService {

  constructor () {
    // start infinite loop for processing requests
    this._tmHandle = setTimeout(this._processRequests, 1);
    // send heartbeat every 40 milliseconds
    this._heartbeatHandle = setInterval(this.sendHeartBeat, 40);
    this._store = new SpotStore();
  }

  private _tmHandle: NodeJS.Timeout;
  private _heartbeatHandle: NodeJS.Timeout;
  private _store: SpotStore;

  sendHeartBeat() {
    console.log("Heartbeat!");
  }

  _processRequests = () => {
    // process all AddRequests immediately
    while (addRequestQueue.length > 0) {
      let addreq: AddRequest = addRequestQueue.pop();
      this._store.add(addreq.ccypair, addreq.spot, addreq.tickTime);
    }

    // take only one getRequest from queue
    let getreq = getRequestQueue.pop();
    if (getreq) {
      // if taken, ask store for value and invoke callback
      const spot = this._store.get(getreq.ccypair, getreq.dateTime);
      getreq.cb(spot);
    }

    this._tmHandle = setTimeout(function () {
      this._processRequests()
    }.bind(this), 1);
  }

}

class SpotStore implements ISpotStore {

  private _store: Record<string, Record <string, Spot[]>> = {};

  /**
   * @param ccypair always 6 chars uppercase, only valid CCY codes. maximum number of different strings is 100X100
   * @param spot just a double value for spot that changed at this tickTime
   * @param tickTime  time when this spot ticks.
   */
  add(ccypair: string, spot: number, tickTime: number): void {
    console.log("Add ", ccypair);
    
    let first  = ccypair.slice(0, 3);
    let second = ccypair.slice(3);

    if (!this._store[first]) 
      this._store[first] = {};
    if (!this._store[first][second])
      this._store[first][second] = [];

    this._store[first][second].push({
      time: tickTime,
      value: spot,
    });
  }

  /**
  * @param ccypair always 6 chars uppercase, only valid CCY codes. maximum number of different strings is 100X100
  * @param dateTime point in time.
  * @return spot value at this given time
  */
  get(ccypair: string, dateTime: number): number {
    
    const first  = ccypair.slice(0, 3);
    const second = ccypair.slice(3);
    
    if (!this._store[first]) return 0;
    
    const spots = this._store[first][second] || []; 
    const index = binarySearch(spots, dateTime, s => s.time)
    
    console.log("Get ", ccypair);

    return spots[index]?.value ?? 0;
  }
}




/**
 *    DRIVER CODE
 */

(() => {
  const processor = new Processor();

  const addRequests: AddRequest[] = [
    {ccypair: "GBPUSD", spot: 1.383452, tickTime: new Date("September 14, 2021 13:51").getTime()},
    {ccypair: "GBPUSD", spot: 1.355555, tickTime: new Date("September 15, 2021 10:50").getTime()},
    {ccypair: "USDRUB", spot: 75.12341, tickTime: new Date("September 16, 2021 10:10").getTime()},
    {ccypair: "USDRUB", spot: 74.23412, tickTime: new Date("September 16, 2021 10:23").getTime()},
    {ccypair: "USDRUB", spot: 74.34533, tickTime: new Date("September 16, 2021 10:24").getTime()},
    {ccypair: "USDRUB", spot: 75.11111, tickTime: new Date("September 16, 2021 10:40").getTime()},
    {ccypair: "USDRUB", spot: 75.22222, tickTime: new Date("September 16, 2021 10:41").getTime()},
    {ccypair: "GBPUSD", spot: 1.464992, tickTime: new Date("September 16, 2021 22:12").getTime()},
  ];

  const getRequests: GetRequest[] = [
    {ccypair: "USDRUB", dateTime: new Date("September 4, 2021 03:10").getTime(), cb: () => {}},
    {ccypair: "USDRUB", dateTime: new Date("September 16, 2021 10:40").getTime(), cb: () => {}},
    {ccypair: "GBPUSD", dateTime: new Date("September 14, 2021 13:59").getTime(), cb: () => {}},
  ];

  // asyncronously send all requests
  const handle = setInterval(() => {
    if (getRequests.length)
      getRequestQueue.push(getRequests.pop());
    if (addRequests.length)
      addRequestQueue.push(addRequests.pop());
    if (!addRequests.length && !getRequests.length)
      clearInterval(handle);
  }, 2)

})()
