import TemplateRenderer from "../../Library/HTMLInserter/HTMLInserter";
import { InsertPoint } from "../../Library/HTMLInserter/InsertPoint";
import HTMLInserter from "../../Library/HTMLInserter/HTMLInserter";
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
        HTMLInserter.insertHTMLString(
            navigationElement,
            InsertPoint.afterbegin,
            nav({title: this.title})
        );
    }
}
