import FocusTrap from 'KeyboardEvent/FocusTrap';
import eventRace from 'Event/eventRace';
import parseCustomPropertyValue from 'CssObjectModel/parseCustomPropertyValue';

const htmlClassName = 'context-menu-open';
const eventOptions: AddEventListenerOptions & EventListenerOptions = { passive: true };

export default class ContextMenu extends HTMLElement {
    public _focusTrap: FocusTrap;
    private _expanded: boolean;
    public _closeButton: HTMLButtonElement | null;
    public _clickListener: any = null;
    public _keydownListener: any = null;
    public _resizeListener: any = null;
    public _floating: boolean | null = null;
    public readonly _floatAtPixels: number | null = null;

    static get observedAttributes(): string[] {
        return ['expanded'];
    }

    constructor() {
        super();

        this._focusTrap = new FocusTrap(this, { treatArrowUpDownAsTabbing: true });
        this._expanded = this.hasAttribute('expanded');
        this._closeButton = this.querySelector('.js-close-button');

        this._floatAtPixels = parseCustomPropertyValue(this, '--float-at');
    }

    public set expanded(value: boolean) {
        this._expanded = value;

        if (value) {
            addHtmlClassName();
            attachEvents.bind(this)();
            this.setAttribute('expanded', '');
            this.setAttribute('aria-hidden', 'false');
            this.focus();
            this._focusTrap.refreshTabbableElements();
            this.dispatchEvent(new CustomEvent('contextmenuexpanded'));
            this.classList.add('animate-in');
            if (null !== this._floatAtPixels) {
                this._floating = (window.innerWidth >= this._floatAtPixels);
            }
            return;
        }

        removeHtmlClassName();
        detachEvents.bind(this)();
        this.classList.remove('animate-in');
        this.classList.add('animate-out');
        this.setAttribute('aria-hidden', 'true');
        this.removeAttribute('expanded');
        removeAnimateClass.bind(this)();

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

function addHtmlClassName(): void {
    document.documentElement.classList.add(htmlClassName);
}

function removeHtmlClassName(): void {
    document.documentElement.classList.remove(htmlClassName);
}

function attachEvents(this: ContextMenu): void {
    this._clickListener = onDocumentClick.bind(this);
    document.addEventListener('click', this._clickListener, eventOptions);

    this._keydownListener = onDocumentKeydown.bind(this);
    document.addEventListener('keydown', this._keydownListener);

    this._resizeListener = onWindowResize.bind(this);
    window.addEventListener('resize', this._resizeListener, eventOptions);
}

function detachEvents(this: ContextMenu): void {
    if (null !== this._clickListener) {
        document.removeEventListener('click', this._clickListener, eventOptions);
        this._clickListener = null;
    }

    if (null !== this._keydownListener) {
        document.removeEventListener('keydown', this._keydownListener);
        this._keydownListener = null;
    }

    if (null !== this._resizeListener) {
        window.removeEventListener('resize', this._resizeListener, eventOptions);
        this._resizeListener = null;
    }
}

function removeAnimateClass(this: ContextMenu): void {
    const removeAnimateClassName = (): void => {
        this.classList.remove('animate-out');
    };
    const endEvent: [EventTarget, string, EventListener] = [this, 'transitionend', removeAnimateClassName];
    const cancelEvent: [EventTarget, string, EventListener] = [this, 'transitioncancel', removeAnimateClassName];
    eventRace(1000, removeAnimateClassName, endEvent, cancelEvent);
}

function onWindowResize(this: ContextMenu, event: Event): void {
    if (null === this._floatAtPixels || null === this._floating) {
        return;
    }

    if (!this._floating && window.innerWidth >= this._floatAtPixels) {
        this._floating = true;
        requestAnimationFrame(() => {
            this._focusTrap.refreshTabbableElements();
        });
        return;
    }

    if (this._floating && window.innerWidth < this._floatAtPixels) {
        this._floating = false;
        requestAnimationFrame(() => {
            this._focusTrap.refreshTabbableElements();
        });
    }
}

function onDocumentKeydown(this: ContextMenu, event: KeyboardEvent): void {
    const esc = (event.key === 'Escape');

    if (esc) {
        this.expanded = false;
        event.preventDefault();
        event.stopImmediatePropagation();
        return;
    }

    this._focusTrap.handleKeydownEvent(event);
}

function onDocumentClick(this: ContextMenu, event: MouseEvent): void {
    if (null === event.target || !(event.target instanceof Node)) {
        return;
    }

    event.stopPropagation();

    // Click outside, close the context menu
    if (this !== event.target && !this.contains(event.target)) {
        this.expanded = false;
    }

    // Click on backdrop (psuedo-element of this element) or close button
    if (this === event.target ||
        null !== this._closeButton && (this._closeButton === event.target || this._closeButton.contains(event.target))
    ) {
        this.expanded = false;
    }
}
