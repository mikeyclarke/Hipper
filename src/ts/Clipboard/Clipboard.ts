export default class Clipboard {
    private readonly clipboardApiSupported: boolean;

    constructor() {
        this.clipboardApiSupported = ('clipboard' in navigator);
    }

    public copyText(text: string): Promise<void> {
        if (this.clipboardApiSupported) {
            return navigator.clipboard.writeText(text);
        }

        const element = this.createTempElement(text);

        document.body.appendChild(element);
        this.copyFromElement(element);
        document.body.removeChild(element);

        return Promise.resolve();
    }

    private copyFromElement(element: HTMLPreElement): void {
        const selection = window.getSelection();
        const range = document.createRange();

        if (null === selection) {
            return;
        }

        selection.removeAllRanges();
        range.selectNodeContents(element);
        selection.addRange(range);

        document.execCommand('copy');
        selection.removeAllRanges();
    }

    private createTempElement(text: string): HTMLPreElement {
        const element = document.createElement('pre');
        element.style.position = 'fixed';
        element.style.bottom = '0';
        element.style.height = '1px';
        element.style.width = '1px';
        element.textContent = text;
        return element;
    }
}
