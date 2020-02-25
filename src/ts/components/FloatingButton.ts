import FocusTrap from 'KeyboardEvent/FocusTrap';

export default class FloatingButton extends HTMLElement {
    public _focusTrap: FocusTrap | null;
    public _expanded: boolean;
    public _button: HTMLButtonElement | null;
    public _list: HTMLOListElement | null;
    public _documentKeydownListener: any;
    public _documentClickListener: EventListener | null;
    public _transitionEndListener: EventListener | null;

    constructor() {
        super();

        this._focusTrap = null;
        this._expanded = this.hasAttribute('expanded');
        this._button = this.querySelector('.js-button');
        this._list = this.querySelector('.js-list');
        this._documentClickListener = null;
        this._documentKeydownListener = null;
        this._transitionEndListener = null;
    }

    public connectedCallback(): void {
        if (!this.isConnected) {
            return;
        }

        this._documentClickListener = onDocumentClick.bind(this);
        document.addEventListener('click', this._documentClickListener);
    }

    public disconnectedCallback(): void {
        if (null === this._documentClickListener) {
            return;
        }

        document.removeEventListener('click', this._documentClickListener);
        this._documentClickListener = null;
    }

    public set expanded(value: boolean) {
        this._expanded = value;

        if (value) {
            this.setAttribute('expanded', 'true');

            if (null !== this._transitionEndListener) {
                this.removeEventListener('transitionend', this._transitionEndListener);
                this._transitionEndListener = null;
            }

            if (null !== this._list) {
                this._list.hidden = false;
            }

            if (null === this._focusTrap) {
                this._focusTrap = new FocusTrap(this, { treatArrowUpDownAsTabbing: true });
            }

            this._documentKeydownListener = onDocumentKeydown.bind(this);
            document.addEventListener('keydown', this._documentKeydownListener);
            return;
        }

        if (null !== this._documentKeydownListener) {
            document.removeEventListener('keydown', this._documentKeydownListener);
        }

        this._transitionEndListener = onTransitionEnd.bind(this);
        this.addEventListener('transitionend', this._transitionEndListener, { once: true });

        this.removeAttribute('expanded');
    }

    public get expanded(): boolean {
        return this._expanded;
    }
}

function onDocumentKeydown(this: FloatingButton, event: KeyboardEvent): void {
    const esc = (event.key === 'Escape');

    if (esc) {
        this.expanded = false;
        event.preventDefault();
        event.stopImmediatePropagation();
        return;
    }

    if (null !== this._focusTrap) {
        this._focusTrap.handleKeydownEvent(event);
    }
}

function onDocumentClick(this: FloatingButton, event: Event): void {
    if (!(event.target instanceof Node)) {
        return;
    }

    if (null === this._button) {
        return;
    }

    if (this.expanded && !this.contains(event.target)) {
        this.expanded = false;
    }

    if (!this.contains(event.target)) {
        return;
    }

    if (event.target !== this._button && !this._button.contains(event.target)) {
        return;
    }

    if (this.expanded) {
        this.expanded = false;
        return;
    }

    this.expanded = true;
}

function onTransitionEnd(this: FloatingButton): void {
    if (null !== this._list) {
        this._list.hidden = true;
    }
}
