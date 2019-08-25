export class DocumentHeadConfigurationProvider {
    private readonly htmlHead: HTMLHeadElement;

    constructor() {
        this.htmlHead = document.head;
    }

    public getValue(selector: string, throwOnNotFound: boolean = true): string | null {
        const element = this.htmlHead.querySelector(selector);
        if (null === element) {
            return this.handleNotFound(selector, throwOnNotFound);
        }

        const value = element.getAttribute('content');
        return value;
    }

    private handleNotFound(selector: string, throwOnNotFound: boolean): null {
        if (throwOnNotFound) {
            throw new Error(`No element in document head matching selector '${selector}'`);
        }
        return null;
    }
}
