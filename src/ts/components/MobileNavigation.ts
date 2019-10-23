import eventRace from 'Event/eventRace';

const bodyClassName = 'navigation-open';

export default class MobileNavigation extends HTMLElement {
    private _open: boolean;
    public _escapeKeyHandler: any = null;
    public _mousedownOutsideHandler: EventListener | null = null;
    public _viewportResizeListener: any = null;
    public readonly _hideAt: string | null = null;
    private readonly _pageContent: HTMLElement | null;

    static get observedAttributes(): string[] {
        return ['open'];
    }

    constructor() {
        super();

        this._open = this.hasAttribute('open');
        this._pageContent = document.querySelector('.js-page-content');

        const hideAt = window.getComputedStyle(this).getPropertyValue('--hide-at').trim();
        this._hideAt = (hideAt !== '') ? hideAt : null;
    }

    public set open(value: boolean) {
        this._open = value;

        if (value) {
            scrollWindowToTop();
            addHtmlClassName();
            addCloseEvents.bind(this)();
            this.hidden = false;
            this.setAttribute('open', '');
            this.setAttribute('tabindex', '0');
            this.setAttribute('aria-hidden', 'false');
            this.focus();
            this.dispatchEvent(new CustomEvent('mobilenavigationopened'));
            return;
        }

        removeHtmlClassName();
        removeCloseEvents.bind(this)();
        this.setAttribute('tabindex', '-1');
        this.setAttribute('aria-hidden', 'true');
        this.removeAttribute('open');

        if (null === this._pageContent) {
            this.hidden = true;
        } else {
            const hide = (event: Event) => {
                if (!this._open && event.target === this._pageContent) {
                    this.hidden = true;
                }
            };
            const endEvent: [EventTarget, string, EventListener] = [this._pageContent, 'transitionend', hide];
            const cancelEvent: [EventTarget, string, EventListener] = [this._pageContent, 'transitioncancel', hide];
            eventRace(1000, hide, endEvent, cancelEvent);
        }

        this.dispatchEvent(new CustomEvent('mobilenavigationclosed'));
    }

    public get open(): boolean {
        return this._open;
    }

    public attributeChangedCallback(name: string, oldValue: string, newValue: string): void {
        if (name === 'open') {
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

            if (null !== value) {
                this.open = value;
            }
        }
    }
}

function scrollWindowToTop(): void {
    window.scrollTo(0, 0);
}

function addHtmlClassName(): void {
    document.documentElement.classList.add(bodyClassName);
}

function removeHtmlClassName(): void {
    document.documentElement.classList.remove(bodyClassName);
}

function addCloseEvents(this: MobileNavigation): void {
    if (null === this._escapeKeyHandler) {
        this._escapeKeyHandler = (event: KeyboardEvent) => {
            if (['Escape', 'Esc'].includes(event.key) &&
                null === this.querySelector('[aria-haspopup][aria-expanded="true"]')
            ) {
                this.open = false;
            }
        };
    }

    if (null === this._mousedownOutsideHandler) {
        this._mousedownOutsideHandler = (event) => {
            if (null === event.target || !(event.target instanceof Node)) {
                return;
            }

            if (!this.contains(event.target) && null === this.querySelector('[aria-haspopup][aria-expanded="true"]')) {
                this.open = false;
            }
        };
    }

    if (null === this._viewportResizeListener && null !== this._hideAt) {
        this._viewportResizeListener = (event: UIEvent) => {
            if (window.matchMedia(`(min-width: ${this._hideAt})`).matches) {
                // Don’t even fucking ask. TypeScript bugs galore.
                const expandedDescendants: any[] = Array.from(this.querySelectorAll('[aria-haspopup][aria-expanded="true"]'));
                for (const element of expandedDescendants) {
                    element.click();
                }
                this.open = false;
            }
        };
    }

    document.addEventListener('keydown', this._escapeKeyHandler);
    document.addEventListener('mousedown', this._mousedownOutsideHandler);
    window.addEventListener('resize', this._viewportResizeListener);
}

function removeCloseEvents(this: MobileNavigation): void {
    if (null !== this._escapeKeyHandler) {
        document.removeEventListener('keydown', this._escapeKeyHandler);
        this._escapeKeyHandler = null;
    }

    if (null !== this._mousedownOutsideHandler) {
        document.removeEventListener('mousedown', this._mousedownOutsideHandler);
        this._mousedownOutsideHandler = null;
    }

    if (null !== this._viewportResizeListener) {
        window.removeEventListener('resize', this._viewportResizeListener);
        this._viewportResizeListener = null;
    }
}
