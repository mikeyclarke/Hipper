import EventDelegate from 'hleo/EventDelegator/EventDelegate';
import EventsHash from 'hleo/EventDelegator/EventsHash';
import EventsEnabled from 'hleo/EventDelegator/EventsEnabled';

export default class EventDelegator {
    private readonly element: HTMLElement;
    private events!: EventsHash;
    private context!: EventsEnabled;
    private readonly eventSplitter: RegExp = /^(\S+)\s*(.*)$/;
    private readonly eventDelegates: IDelegateCollection = {};
    private readonly boundHandler: EventListenerOrEventListenerObject;
    private eventTypes!: string[];

    constructor(el: HTMLElement) {
        this.boundHandler = this.handleEvent.bind(this);
        this.element = el;
    }

    public delegate(): void {
        this.setEvents();
        this.eventTypes = this.getEventTypes();
        this.storeEventTypes();
        this.bindTopLevelEvents();
    }

    public undelegate(): void {
        for (const evtType of this.eventTypes) {
            this.element.removeEventListener(evtType, this.boundHandler);
        }
    }

    public setContext(context: EventsEnabled): void {
        this.context = context;
    }

    public setEvents(): void {
        this.events = this.context.getEvents();
    }

    private handleEvent(event: Event): void {
        if (this.isEventTypeRegistered(event.type)) {
            for (const registeredEvent of this.eventDelegates[event.type]) {
                if (!registeredEvent.selector) {
                    // @ts-ignore
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
            const nextNode = <HTMLElement> currentNode.parentElement;
            if (nextNode !== null) {
                currentNode = nextNode;
            } else {
                break;
            }
        }
        if (currentNode !== this.element && currentNode.classList.contains(delegate.selector)) {
            // @ts-ignore
            this.context[delegate.callback](event);
        }
    }

    private bindTopLevelEvents(): void {
        for (const evtType of this.eventTypes) {
            this.element.addEventListener(evtType, this.boundHandler);
        }
    }

    private getEventTypes(): string[] {
        const types = [];

        for (const eventSignature of Object.keys(this.events)) {
            const splitEvent = eventSignature.match(this.eventSplitter);
            if (splitEvent && splitEvent.length > 0) {
                const eventType = splitEvent[1];
                types.push(eventType);
            }
        }

        return types.filter((value, index, self) => {
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
            if (match) {
                this.eventDelegates[match[1]].push(new EventDelegate(match[2], this.events[match[0]], match[1]));
            }
        }
    }
}

interface IDelegateCollection {
    [key: string]: EventDelegate[];
}
