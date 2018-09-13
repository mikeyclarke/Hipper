class ElementCache
{
    private baseElement: HTMLElement;
    private baseElementSelector: string;
    private cachedElements: any = {};

    constructor(baseElementSelector: string, elementHash: any)
    {
        this.baseElementSelector = baseElementSelector;
        this.setBaseElement();
        this.cacheElements(elementHash);
    }

    public get(key): HTMLElement
    {
        return this.cachedElements[key];
    }

    private setBaseElement(): void
    {
        const matches = document.querySelectorAll(this.baseElementSelector);
        this.requireDistinctElement(matches);
        this.baseElement = <HTMLElement>  matches.item(0);
    }

    private cacheElements(elementHash): void
    {
        for (let selector in elementHash)
        {
            if (elementHash[selector] === this.baseElementSelector) {
                this.cachedElements[selector] = this.baseElement;
            } else {
                this.cachedElements[selector] = this.querySelectorDistinct(elementHash[selector]);
            }
        }
    }

    private querySelectorDistinct(selector: string)
    {
        const matches = this.baseElement.querySelectorAll(selector);
        this.requireDistinctElement(matches);
        return matches.item(0);
    }

    private requireDistinctElement(queryResult: NodeList): void
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

export default ElementCache;
