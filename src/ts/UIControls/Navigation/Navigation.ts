import Template from '../../Library/Template/Template';
export class Navigation {
    private title: string = 'hleo';
    private el: string;
    public static template: Template;

    constructor(el: string)
    {
        this.el = el;
    }

    public render(): void
    {
        const navigationElement = document.querySelector(this.el);
        const templateHTML = Navigation.template.render({title: this.title});
        navigationElement.insertAdjacentHTML('afterbegin', templateHTML);
    }
}
