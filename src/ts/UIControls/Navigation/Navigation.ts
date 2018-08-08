import TemplateRenderer from "../../Library/TemplateRenderer/TemplateRenderer";
import { InsertPosition } from "../../Library/TemplateRenderer/InsertPosition";
const nav = require('Twig/navigation.twig');


export class Navigation {
    private title: string;
    constructor(title: string)
    {
        this.title = title;
    }

    public getTitle(): string
    {
        return this.title;
    }

    public render(): void
    {
        const navigationElement = document.querySelector('.js-navigation-container');
        TemplateRenderer.render({
            template: nav, 
            anchorElement: navigationElement, 
            data: {title: this.title}, 
            position: InsertPosition.afterbegin,
        });
    }
}
