import Template from '../../Library/Template/Template';
export class Navigation {
    public static template: Template;
    public static Render;
    public static Events;
    public ui: any;
    public eventManager: any;
    private title: string = 'hleo';
    private events: Array<any> = [
        {
            selector: '.js-button',
            type: 'click',
            handler: 'buttonClicked',
        }
    ];

    constructor(el: string)
    {
        this.eventManager = new Navigation.Events();
        this.ui = new Navigation.Render(Navigation.template, el, this.eventManager);
        if (this.ui.element) {
            this.eventManager.bindEvents(this.ui, this.events, this);
        }
        this.render({title: 'test'});
    }

    public render(data)
    {
        this.ui.render(data);
    }

    public buttonClicked ()
    {
        console.log('hhh');
    }
}
