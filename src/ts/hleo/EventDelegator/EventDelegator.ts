class EventDelegator
{
    private element: HTMLElement;
    private events: object;
    private context: any;
    private eventSplitter: RegExp = /^(\S+)\s*(.*)$/;
    private eventTypes: Array<string>;
    private eventMap: any = {};
    private boundHandler: any;

    constructor(events: object, el: HTMLElement, context: any)
    {
        this.boundHandler = this.handleEvent.bind(this);
        this.element = el;
        this.events = events;
        this.context = context;
    }

    public bindEvents()
    {
        this.getEventTypes();
        this.createEventMap();
        this.bindTopLevelEvents();
    }

    public removeEvents()
    {
        this.eventTypes.forEach((evtType) => {
            this.element.removeEventListener(evtType, this.boundHandler);
        });
    }

    private handleEvent(event): void
    {
        for (let registeredEventType in this.eventMap)
        {
            if (event.type === registeredEventType)
            {
                this.eventMap[registeredEventType].forEach(registeredEvent => {
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

    private searchParentsForMatch(registeredEvent, currentEvent)
    {
        let el = currentEvent.target;
        while (el && !el.classList.contains(registeredEvent.selector) && el !== this.element.parentNode) {
            el = el.parentNode
        }
        if (el !== this.element && el.classList.contains(registeredEvent.selector)) {
            this.context[registeredEvent.callback]();
        }
    }

    private bindTopLevelEvents()
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

    private createEventMap()
    {
        this.eventTypes.forEach((evtType) => {
            this.eventMap[evtType] = [];
        });
        this.populateEventMap();
    }

    private populateEventMap()
    {
        for (let key in this.events)
        {
            const match = key.match(this.eventSplitter);
            this.eventMap[match[1]].push({
                'callback': this.events[match[0]],
                'type': match[1],
                'selector': match[2].substr(1),
            });
        }
    }
}

export default EventDelegator;
