import ElementHash from 'hleo/ElementCache/ElementHash';

/** Provides an API to store and retrieve DOM elements associated with a component */
export default class ElementCache {
    private readonly baseElement: HTMLElement;
    private readonly baseElementSelector: string;
    private readonly cachedElements: IElementCache = {};

    /**
     * Create an ElementCache
     * @param baseElementSelector - CSS selector for the base element
     * @param elementHash - A hash of keys and css selectors for later retrieval
     */
    constructor(baseElementSelector: string, elementHash: ElementHash) {
        this.baseElementSelector = baseElementSelector;
        this.baseElement = this.getBaseElement();
        this.cacheElements(elementHash);
    }

    public get(key: string): HTMLElement {
        return this.cachedElements[key];
    }

    private getBaseElement(): HTMLElement {
        const matches = document.querySelectorAll(this.baseElementSelector);
        this.requireDistinctElement(matches);
        return <HTMLElement> matches.item(0);
    }

    private cacheElements(elementHash: ElementHash): void {
        for (const selector in elementHash) {
            if (elementHash[selector] === this.baseElementSelector) {
                this.cachedElements[selector] = this.baseElement;
            } else {
                this.cachedElements[selector] = this.querySelectorDistinct(elementHash[selector]);
            }
        }
    }

    private querySelectorDistinct(selector: string): HTMLElement {
        const matches = this.baseElement.querySelectorAll(selector);
        this.requireDistinctElement(matches);
        return <HTMLElement> matches.item(0);
    }

    private requireDistinctElement(queryResult: NodeList): void {
        if (queryResult.length > 1) {
            throw new Error('Selector matched multiple elements');
        }
        if (queryResult.length === 0) {
            throw new Error('Unable to match selector');
        }
    }
}

interface IElementCache {
    [key: string]: HTMLElement;
}
