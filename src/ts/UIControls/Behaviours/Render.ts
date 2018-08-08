import Template from '../../Library/Template/Template';

export class Render {
    public template: Template;
    public el: string;
    public element: Element;
    private eventManager: any;

    constructor(template: Template, el: string, eventManager?: any)
    {
        this.template = template;
        this.el = el;
        this.eventManager = eventManager;
        this.setElement();
    }

    private setElement()
    {
        if (!this.element) {
            this.element = document.querySelector(this.el) || null;
        }
    }

    public render(data: any): void
    {
        if (this.element) {
            this.rerender(data);
            return;
        }
        this.removeExistingNode();
        const parentNode = document.querySelector(this.el);
        const templateHTML = this.template.render(data);
        parentNode.insertAdjacentHTML('afterbegin', templateHTML);
        this.setElement();
    }

    public rerender(data: any): void
    {
        this.removeExistingNode();
        const parentNode = document.querySelector(this.el);
        const templateHTML = this.template.render(data);
        parentNode.insertAdjacentHTML('afterbegin', templateHTML);
        this.setElement();
        if (this.eventManager) {
            this.eventManager.rebindEvents();
        }
    }

    private removeExistingNode()
    {
        while (this.element.firstChild) {
            this.element.removeChild(this.element.firstChild);
        }
    }
}
