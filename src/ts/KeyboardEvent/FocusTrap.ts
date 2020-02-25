import * as tabbable from 'tabbable';

interface Options {
    treatArrowUpDownAsTabbing?: boolean;
}

const DEFAULT_OPTIONS: Options = {
    treatArrowUpDownAsTabbing: false,
};

export default class FocusTrap {
    private containingElement: HTMLElement;
    private options: Options;
    private tabbableElements: HTMLElement[];

    constructor(
        containingElement: HTMLElement,
        options: Options = {}
    ) {
        this.containingElement = containingElement;

        const mergedOptions = { ...DEFAULT_OPTIONS, ...options };
        this.options = mergedOptions;

        this.tabbableElements = tabbable(this.containingElement);
    }

    public refreshTabbableElements(): void {
        this.tabbableElements = tabbable(this.containingElement);
    }

    public handleKeydownEvent(event: KeyboardEvent): void {
        const arrowTabs = this.options.treatArrowUpDownAsTabbing;
        const tabUp = ((arrowTabs && event.key === 'ArrowUp') || (event.key === 'Tab' && event.shiftKey));
        const tabDown = ((arrowTabs && event.key === 'ArrowDown') || (event.key === 'Tab' && !event.shiftKey));

        if (tabUp || tabDown) {
            if (null === document.activeElement) {
                return;
            }

            const activeElementIndex = this.tabbableElements.indexOf(<HTMLElement> document.activeElement);
            const lastIndex = this.tabbableElements.length - 1;
            let nextElement = null;

            if ([-1, 0].includes(activeElementIndex) && tabUp) {
                nextElement = this.tabbableElements[lastIndex];
            } else if ([-1, lastIndex].includes(activeElementIndex) && tabDown) {
                nextElement = this.tabbableElements[0];
            } else {
                const nextIndex = (tabDown) ? activeElementIndex + 1 : activeElementIndex - 1;
                nextElement = this.tabbableElements[nextIndex];
            }

            nextElement.focus();
            event.preventDefault();
        }
    }
}
