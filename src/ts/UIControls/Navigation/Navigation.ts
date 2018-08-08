import Template from '../../Library/Template/Template';
export class Navigation {
    private title: string = 'hleo';
    public static template: Template;

    public getTitle(): string
    {
        return this.title;
    }

    public render(): void
    {
        const navigationElement = document.querySelector('.js-navigation-container');
        const templateHTML = Navigation.template.render({title: this.title});
        navigationElement.insertAdjacentHTML('afterbegin', templateHTML);
    }
}
