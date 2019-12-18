import Clipboard from 'Clipboard/Clipboard';
import timeout from 'Timeout/timeout';

const confirmClassName = 'c-copy-link__confirm';
const confirmLabelClassName = 'c-copy-link__confirm-label';
const defaultConfirmLabel = 'Copied to clipboard';
const showForMilliseconds = 3000;

export default class CopyLink extends HTMLElement {
    public _clipboard: Clipboard;
    private _button: HTMLButtonElement | null = null;
    private _clickEventListener: EventListener | null = null;

    constructor() {
        super();

        this._clipboard = new Clipboard();
    }

    public connectedCallback(): void {
        if (!this.isConnected) {
            return;
        }

        const button = this.querySelector('.js-button');
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }
        this._button = button;

        this._clickEventListener = copyLink.bind(this);
        this._button.addEventListener('click', this._clickEventListener);
    }

    public disconnectedCallback(): void {
        if (null !== this._button && null !== this._clickEventListener) {
            this._button.removeEventListener('click', this._clickEventListener);
        }

        this._button = null;
        this._clickEventListener = null;
    }
}

async function copyLink(this: CopyLink): Promise<void> {
    if (!this.hasAttribute('link')) {
        return;
    }

    await this._clipboard.copyText(<string> this.getAttribute('link'));
    notify.bind(this)();
}

async function notify(this: CopyLink): Promise<void> {
    const text = this.getAttribute('confirm-label') ?? defaultConfirmLabel;
    const element = document.createElement('div');
    element.innerHTML = `<span class="${confirmLabelClassName}">${text}</span>`;
    element.className = confirmClassName;
    this.appendChild(element);

    await timeout(50);
    element.classList.add('show');

    await timeout(showForMilliseconds);

    element.addEventListener('transitionend', () => {
        element.remove();
    });
    element.classList.remove('show');
}
