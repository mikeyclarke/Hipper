import ContextMenu from 'components/ContextMenu';

const clickOptions: AddEventListenerOptions & EventListenerOptions = { passive: true };

export default class ContextMenuToggle extends HTMLElement {
    public _button: HTMLButtonElement;
    public _buttonClickListener: any;
    public _contextMenu: ContextMenu;

    constructor() {
        super();

        this._buttonClickListener = null;

        const button = this.querySelector('.js-button');
        if (null === button) {
            throw new Error('Button element does not exist');
        }

        this._button = <HTMLButtonElement> button;

        const contextMenuId = this._button.getAttribute('aria-controls');
        if (null === contextMenuId) {
            throw new Error('Button is not associated with a context-menu element');
        }

        const contextMenu = document.getElementById(contextMenuId);
        if (null === contextMenu) {
            throw new ReferenceError('Mobile-navigation element does not exist');
        }

        if (!(contextMenu instanceof ContextMenu)) {
            throw new TypeError('Element is not a ContextMenu element');
        }

        this._contextMenu = contextMenu;
    }

    public connectedCallback(): void {
        if (!this.isConnected) {
            return;
        }

        if (null === this._button) {
            return;
        }

        this._buttonClickListener = onButtonClick.bind(this);
        document.addEventListener('click', this._buttonClickListener, clickOptions);
    }

    public disconnectedCallback(): void {
        if (null === this._buttonClickListener) {
            return;
        }

        if (null === this._button) {
            return;
        }

        document.removeEventListener('click', this._buttonClickListener, clickOptions);
    }
}

function onButtonClick(this: ContextMenuToggle, event: MouseEvent): void {
    if (null === event.target || !(event.target instanceof Node)) {
        return;
    }

    if (!this.contains(event.target)) {
        return;
    }

    if (this._contextMenu.expanded) {
        this._contextMenu.expanded = false;
        return;
    }

    this._contextMenu.addEventListener('contextmenucontracted', () => {
        this._button.setAttribute('aria-expanded', 'false');
    }, { once: true });

    this._button.setAttribute('aria-expanded', 'true');
    this._contextMenu.expanded = true;
}
