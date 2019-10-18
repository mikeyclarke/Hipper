import MobileNavigation from 'components/MobileNavigation';

export default class MobileNavigationToggle extends HTMLElement {
    public _button: HTMLButtonElement;
    public _buttonClickListener: EventListener | null;
    public _mobileNavigation: MobileNavigation;

    constructor() {
        super();

        this._buttonClickListener = null;

        const button = this.querySelector('.js-button');
        if (null === button) {
            throw new Error('Button element does not exist');
        }

        this._button = <HTMLButtonElement> button;

        const navId = this._button.getAttribute('aria-controls');
        if (null === navId) {
            throw new Error('Button is not associated with a mobile-navigation element');
        }

        const mobileNavigation = document.getElementById(navId);
        if (null === mobileNavigation) {
            throw new Error('Mobile-navigation element does not exist');
        }

        if (!(mobileNavigation instanceof MobileNavigation)) {
            throw new Error('Element is not a MobileNavigation element');
        }

        this._mobileNavigation = mobileNavigation;
    }

    public connectedCallback(): void {
        if (!this.isConnected) {
            return;
        }

        if (null === this._button) {
            return;
        }

        this._buttonClickListener = onButtonClick.bind(this);
        this._button.addEventListener('click', this._buttonClickListener);
    }

    public disconnectedCallback(): void {
        if (null === this._buttonClickListener) {
            return;
        }

        if (null === this._button) {
            return;
        }

        this._button.removeEventListener('click', this._buttonClickListener);
    }
}

function onButtonClick(this: MobileNavigationToggle): void {
    if (this._mobileNavigation.open) {
        this._mobileNavigation.open = false;
        return;
    }

    this._mobileNavigation.addEventListener('mobilenavigationclosed', () => {
        this._button.focus();
    }, { once: true });

    this._mobileNavigation.open = true;
}
