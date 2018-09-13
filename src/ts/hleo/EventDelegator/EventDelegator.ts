import EventDelegate from "./EventDelegate";

class EventDelegator
{
    private element: HTMLElement;
    private events: object;
    private context: any;
    private eventSplitter: RegExp = /^(\S+)\s*(.*)$/;
    private eventTypes: Array<string>;
    private eventDelegates: any = {};
    private boundHandler: any;

    constructor(events: object, el: HTMLElement, context: any)
    {
        this.boundHandler = this.handleEvent.bind(this);
        this.element = el;
        this.events = events;
        this.context = context;
    }

    public bindEvents(): void
    {
        this.getEventTypes();
        this.storeEventTypes();
        this.bindTopLevelEvents();
    }

    public removeEvents(): void
    {
        this.eventTypes.forEach((evtType) => {
            this.element.removeEventListener(evtType, this.boundHandler);
        });
    }

    private handleEvent(event: Event): void
    {
        for (let registeredEventType in this.eventDelegates)
        {
            if (event.type === registeredEventType)
            {
                this.eventDelegates[registeredEventType].forEach(registeredEvent => {
                    if (!registeredEvent.selector)
                    {
                        this.context[registeredEvent.callback]();
                    } else {
                        this.searchParentsForMatch(registeredEvent, event);
                    }
                });
            }
        }
    }

    private searchParentsForMatch(registeredEvent: EventDelegate, currentEvent: Event): void
    {
        let el = <HTMLElement> currentEvent.target;
        while (el && !el.classList.contains(registeredEvent.selector) && el !== this.element.parentNode) {
            el = el.parentElement
        }
        if (el !== this.element && el.classList.contains(registeredEvent.selector)) {
            this.context[registeredEvent.callback]();
        }
    }

    private bindTopLevelEvents(): void
    {
        this.eventTypes.forEach((evtType) => {
            this.element.addEventListener(evtType, this.boundHandler);
        });
    }

    private getEventTypes(): void
    {
        const types = [];

        for (let key in this.events)
        {
            types.push(key.match(this.eventSplitter)[1]);
        }

        this.eventTypes = types.filter((value, index, self) => {
            return self.indexOf(value) === index;
        });
    }

    private storeEventTypes(): void
    {
        this.eventTypes.forEach((evtType) => {
            this.eventDelegates[evtType] = [];
        });
        this.createEventDelegates();
    }

    private createEventDelegates(): void
    {
        for (let key in this.events)
        {
            const match = key.match(this.eventSplitter);
            this.eventDelegates[match[1]].push(new EventDelegate(match[2], this.events[match[0]], match[1]));
        }
    }
}

export default EventDelegator;
