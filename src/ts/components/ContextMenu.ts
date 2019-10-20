import * as tabbable from 'tabbable';

const htmlClassName = 'context-menu-open';

export default class ContextMenu extends HTMLElement {
    private _expanded: boolean;
    public _closeButton: HTMLButtonElement | null;
    public _clickListener: any = null;
    public _keydownListener: any = null;
    public _tabbableElements: HTMLElement[] = [];

    static get observedAttributes(): string[] {
        return ['expanded'];
    }

    constructor() {
        super();

        this._expanded = this.hasAttribute('expanded');
        this._closeButton = this.querySelector('.js-close-button');

    }

    public set expanded(value: boolean) {
        this._expanded = value;

        if (value) {
            addHtmlClassName();
            attachEvents.bind(this)();
            this.setAttribute('expanded', '');
            this.setAttribute('aria-hidden', 'false');
            this.focus();
            cacheTabbableDescendants.bind(this)();
            this.dispatchEvent(new CustomEvent('contextmenuexpanded'));
            window.requestAnimationFrame(() => {
                this.classList.add('animate-in');
            });
            return;
        }

        removeHtmlClassName();
        detachEvents.bind(this)();
        this.classList.remove('animate-in');
        this.classList.add('animate-out');
        this.setAttribute('aria-hidden', 'true');
        this.removeAttribute('expanded');
        this.addEventListener('transitionend', () => {
            this.classList.remove('animate-out');
        }, { once: true });
        this.dispatchEvent(new CustomEvent('contextmenucontracted'));
    }

    public get expanded(): boolean {
        return this._expanded;
    }

    public attributeChangedCallback(name: string, oldValue: string, newValue: string): void {
        if (name === 'expanded') {
            let value = null;

            if (newValue === oldValue) {
                return;
            }

            if (newValue === null) {
                value = false;
            }

            if (newValue === '') {
                value = true;
            }

            if (null !== value && value !== this._expanded) {
                this.expanded = value;
            }
        }
    }
}

function cacheTabbableDescendants(this: ContextMenu): void {
    this._tabbableElements = tabbable(this);
}

function addHtmlClassName(): void {
    document.documentElement.classList.add(htmlClassName);
}

function removeHtmlClassName(): void {
    document.documentElement.classList.remove(htmlClassName);
}

function attachEvents(this: ContextMenu): void {
    this._clickListener = onDocumentClick.bind(this);
    document.addEventListener('click', this._clickListener);

    this._keydownListener = onDocumentKeydown.bind(this);
    document.addEventListener('keydown', this._keydownListener);
}

function detachEvents(this: ContextMenu): void {
    if (null !== this._clickListener) {
        document.removeEventListener('click', this._clickListener);
        this._clickListener = null;
    }

    if (null !== this._keydownListener) {
        document.removeEventListener('keydown', this._keydownListener);
        this._keydownListener = null;
    }
}

function onDocumentKeydown(this: ContextMenu, event: KeyboardEvent): void {
    const esc = (event.key === 'Escape');
    const tabUp = (event.key === 'ArrowUp' || (event.key === 'Tab' && event.shiftKey));
    const tabDown = (event.key === 'ArrowDown' || (event.key === 'Tab' && !event.shiftKey));

    if (esc) {
        this.expanded = false;
        event.preventDefault();
        event.stopImmediatePropagation();
        return;
    }

    if (tabUp || tabDown) {
        if (null === document.activeElement) {
            return;
        }

        const activeElementIndex = this._tabbableElements.indexOf(<HTMLElement> document.activeElement);
        const lastIndex = this._tabbableElements.length - 1;
        let nextElement = null;

        if ([-1, 0].includes(activeElementIndex) && tabUp) {
            nextElement = this._tabbableElements[lastIndex];
        } else if ([-1, lastIndex].includes(activeElementIndex) && tabDown) {
            nextElement = this._tabbableElements[0];
        } else {
            const nextIndex = (tabDown) ? activeElementIndex + 1 : activeElementIndex - 1;
            nextElement = this._tabbableElements[nextIndex];
        }

        nextElement.focus();
        event.preventDefault();
    }
}

function onDocumentClick(this: ContextMenu, event: MouseEvent): void {
    if (null === event.target || !(event.target instanceof Node)) {
        return;
    }

    // Click outside, close the context menu
    if (this !== event.target && !this.contains(event.target)) {
        this.expanded = false;
    }

    event.stopPropagation();

    // Click on backdrop (psuedo-element of this element) or close button
    if (this === event.target ||
        null !== this._closeButton && (this._closeButton === event.target || this._closeButton.contains(event.target))
    ) {
        this.expanded = false;
    }
}
