const bodyClassName = 'navigation-open';

export default class MobileNavigation extends HTMLElement {
    private _open: boolean;
    public _escapeKeyHandler: any = null;
    public _mousedownOutsideHandler: EventListener | null = null;
    private readonly _pageContent: HTMLElement | null;

    static get observedAttributes(): string[] {
        return ['open'];
    }

    constructor() {
        super();

        this._open = this.hasAttribute('open');
        this._pageContent = document.querySelector('.js-page-content');
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
            this._pageContent.addEventListener('transitionend', (event) => {
                if (!this._open && event.target === this._pageContent) {
                    this.hidden = true;
                }
            }, { once: true });
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
            if (['Escape', 'Esc'].includes(event.key)) {
                this.open = false;
            }
        };
    }

    if (null === this._mousedownOutsideHandler) {
        this._mousedownOutsideHandler = (event) => {
            if (null !== event.target && event.target instanceof Node && !this.contains(event.target)) {
                this.open = false;
            }
        };
    }

    document.addEventListener('keyup', this._escapeKeyHandler);
    document.addEventListener('mousedown', this._mousedownOutsideHandler);
}

function removeCloseEvents(this: MobileNavigation): void {
    if (null !== this._escapeKeyHandler) {
        document.removeEventListener('keyup', this._escapeKeyHandler);
        this._escapeKeyHandler = null;
    }

    if (null !== this._mousedownOutsideHandler) {
        document.removeEventListener('mousedown', this._mousedownOutsideHandler);
        this._mousedownOutsideHandler = null;
    }
}
