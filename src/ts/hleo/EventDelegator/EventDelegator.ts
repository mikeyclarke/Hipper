import { EventDelegate } from './EventDelegate';
import { IEvents } from './IEvents';

export class EventDelegator {
    private readonly element: HTMLElement;
    private readonly events: IEvents;
    private readonly context: any;
    private readonly eventSplitter: RegExp = /^(\S+)\s*(.*)$/;
    private readonly eventDelegates: any = {};
    private readonly boundHandler: any;
    private eventTypes: string[];

    constructor(events: IEvents, el: HTMLElement, context: any) {
        this.boundHandler = this.handleEvent.bind(this);
        this.element = el;
        this.events = events;
        this.context = context;
    }

    public delegate(): void {
        this.getEventTypes();
        this.storeEventTypes();
        this.bindTopLevelEvents();
    }

    public undelegate(): void {
        for (const evtType of this.eventTypes) {
            this.element.removeEventListener(evtType, this.boundHandler);
        }
    }

    private handleEvent(event: Event): void {
        if (this.isEventTypeRegistered(event.type)) {
            for (const registeredEvent of this.eventDelegates[event.type]) {
                if (!registeredEvent.selector) {
                    this.context[registeredEvent.callback](event);
                } else {
                    this.searchParentsForMatch(registeredEvent, event);
                }
            }
        }
    }

    private isEventTypeRegistered(type: string): boolean {
        return this.eventDelegates.hasOwnProperty(type);
    }

    private searchParentsForMatch(delegate: EventDelegate, event: Event): void {
        let currentNode = <HTMLElement> event.target;
        while (currentNode && !currentNode.classList.contains(delegate.selector) && currentNode !== this.element.parentNode) {
            currentNode = currentNode.parentElement;
        }
        if (currentNode !== this.element && currentNode.classList.contains(delegate.selector)) {
            this.context[delegate.callback](event);
        }
    }

    private bindTopLevelEvents(): void {
        for (const evtType of this.eventTypes) {
            this.element.addEventListener(evtType, this.boundHandler);
        }
    }

    private getEventTypes(): void {
        const types = [];

        for (const eventSignature of Object.keys(this.events)) {
            types.push(eventSignature.match(this.eventSplitter)[1]);
        }

        this.eventTypes = types.filter((value, index, self) => {
            return self.indexOf(value) === index;
        });
    }

    private storeEventTypes(): void {
        for (const evtType of this.eventTypes) {
            this.eventDelegates[evtType] = [];
        }

        this.createEventDelegates();
    }

    private createEventDelegates(): void {
        for (const eventSignature of Object.keys(this.events)) {
            const match = eventSignature.match(this.eventSplitter);
            this.eventDelegates[match[1]].push(new EventDelegate(match[2], this.events[match[0]], match[1]));
        }
    }
}
