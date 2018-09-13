class EventDelegate
{
    public selector: string;
    public callback: string;
    public eventType: string;

    constructor(selector: string, callback: string, eventType: string)
    {
        this.selector = selector.substr(1);
        this.callback = callback;
        this.eventType = eventType;
    }
}

export default EventDelegate;
