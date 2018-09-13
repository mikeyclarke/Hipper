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

    public delegate(): void
    {
        this.getEventTypes();
        this.storeEventTypes();
        this.bindTopLevelEvents();
    }

    public undelegate(): void
    {
        for (let evtType of this.eventTypes)
        {
            this.element.removeEventListener(evtType, this.boundHandler);
        }
    }

    private handleEvent(event: Event): void
    {
        if (this.isEventTypeRegistered(event.type))
        {
            for (let registeredEvent of this.eventDelegates[event.type])
                {
                    if (!registeredEvent.selector)
                    {
                        this.context[registeredEvent.callback](event);
                    } else {
                        this.searchParentsForMatch(registeredEvent, event);
                    }
                }
        }
    }

    private isEventTypeRegistered(type: string): boolean
    {
        return this.eventDelegates.hasOwnProperty(event.type);
    }

    private searchParentsForMatch(delegate: EventDelegate, event: Event): void
    {
        let currentNode = <HTMLElement> event.target;
        while (currentNode && !currentNode.classList.contains(delegate.selector) && currentNode !== this.element.parentNode) {
            currentNode = currentNode.parentElement
        }
        if (currentNode !== this.element && currentNode.classList.contains(delegate.selector)) {
            this.context[delegate.callback](event);
        }
    }

    private bindTopLevelEvents(): void
    {
        for (let evtType of this.eventTypes)
        {
            this.element.addEventListener(evtType, this.boundHandler);
        }
    }

    private getEventTypes(): void
    {
        const types = [];

        for (let eventSignature in this.events)
        {
            types.push(eventSignature.match(this.eventSplitter)[1]);
        }

        this.eventTypes = types.filter((value, index, self) => {
            return self.indexOf(value) === index;
        });
    }

    private storeEventTypes(): void
    {
        for (let evtType of this.eventTypes)
        {
            this.eventDelegates[evtType] = [];
        }

        this.createEventDelegates();
    }

    private createEventDelegates(): void
    {
        for (let eventSignature in this.events)
        {
            const match = eventSignature.match(this.eventSplitter);
            this.eventDelegates[match[1]].push(new EventDelegate(match[2], this.events[match[0]], match[1]));
        }
    }
}

export default EventDelegator;
