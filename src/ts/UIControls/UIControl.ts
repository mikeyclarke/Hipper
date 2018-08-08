import Template from '../Library/Template/Template';
import IControl from './IControl';

export default abstract class UIControl implements IControl {
    abstract template: any;
    protected abstract events: Array<any>;
    protected el: string;
    protected element: Element;

    constructor(el: string)
    {
        this.el = el;
        this.setElement();
    }

    public setElement()
    {
        if (!this.element) {
            this.element = document.querySelector(this.el) || null;
        }
    }

    public render(data: any)
    {
        this.removeExistingNode();
        const parentNode = document.querySelector(this.el);
        const templateHTML = this.template.render(data);
        parentNode.insertAdjacentHTML('afterbegin', templateHTML);
        this.setElement();
    }

    public bindEvents()
    {
        for (let event in this.events) {
            try {
                this.element.querySelector(this.events[event].selector)
                .addEventListener(this.events[event].type, this[this.events[event].handler]);
            } catch (e) {
                throw new Error(`event binding failed on "${this.events[event].selector} ${this.events[event].type} ${this.events[event].handler}"`);
            }
        }
    }

    private removeExistingNode()
    {
        while (this.element.firstChild) {
            this.element.removeChild(this.element.firstChild);
        }
    }
}
