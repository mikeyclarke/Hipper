class UIElement
{
    private element: HTMLElement;
    private elements: any = {};
    private selector: string;

    constructor(selector: string, elements: any)
    {
        this.selector = selector;
        this.setElement();
        this.cacheElements(elements);
    }

    public getElement(): HTMLElement
    {
        return this.element;
    }

    public get(key): HTMLElement
    {
        return this.elements[key];
    }

    private setElement(): void
    {
        const matches = this.getElementMatches(this.selector);
        this.checkQueryResult(matches);
        this.element = <HTMLElement> matches.item(0);
    }

    private cacheElements(elements): void
    {
        for (let key in elements)
        {
            const matches = this.getElementMatches(elements[key]);
            this.checkQueryResult(matches);
            this.elements[key] = this.element.querySelector(elements[key]);
        }
    }

    private getElementMatches(selector: string): NodeList
    {
        return document.querySelectorAll(selector);
    }

    private checkQueryResult(queryResult: NodeList): void
    {
        if (queryResult.length > 1) 
        {
            throw new Error('Selector matched multiple elements');
        }
        if (queryResult.length === 0)
        {
            throw new Error('Unable to match selector');
        }
    }
}

export default UIElement;
