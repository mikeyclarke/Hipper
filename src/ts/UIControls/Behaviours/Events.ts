export class Events {
    private ui;
    private events;
    private context;

    public bindEvents(ui, events, context)
    {
        this.ui = ui;
        this.events = events;
        this.context = context;

        for (let event in events) {
            try {
                ui.element.querySelector(events[event].selector)
                .addEventListener(events[event].type, context[events[event].handler]);
            } catch (e) {
                throw new Error(`event binding failed on "${events[event].selector} ${events[event].type} ${events[event].handler}"`);
            }
        }
    }

    public rebindEvents()
    {
        for (let event in this.events) {
            try {
                this.ui.element.querySelector(this.events[event].selector)
                .addEventListener(this.events[event].type, this.context[this.events[event].handler]);
            } catch (e) {
                throw new Error('event binding failed');
            }
        }
    }
}
