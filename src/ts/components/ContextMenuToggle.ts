import ContextMenu from 'components/ContextMenu';

const clickOptions: AddEventListenerOptions & EventListenerOptions = { passive: true };

export default class ContextMenuToggle extends HTMLElement {
    public _button: HTMLButtonElement | null = null;
    public _buttonClickListener: any;
    public _contextMenu: ContextMenu | null = null;

    constructor() {
        super();

        this._buttonClickListener = null;
    }

    public connectedCallback(): void {
        if (!this.isConnected) {
            return;
        }

        if (null === this._button) {
            const button = this.querySelector('.js-button');
            if (null === button) {
                throw new Error('Button element does not exist');
            }

            this._button = <HTMLButtonElement> button;
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

    if (null === this._button) {
        return;
    }

    if (null === this._contextMenu) {
        const contextMenu = getContextMenu.bind(this)();
        if (null === contextMenu) {
            return;
        }
        this._contextMenu = contextMenu;
    }

    if (!this.contains(event.target)) {
        return;
    }

    if (this._contextMenu.expanded) {
        this._contextMenu.expanded = false;
        return;
    }

    this._contextMenu.addEventListener('contextmenucontracted', () => {
        if (null === this._button) {
            return;
        }

        this._button.setAttribute('aria-expanded', 'false');
    }, { once: true });

    this._button.setAttribute('aria-expanded', 'true');
    this._contextMenu.expanded = true;
}

function getContextMenu(this: ContextMenuToggle): ContextMenu | null {
    if (null === this._button) {
        throw new Error('Button element does not exist');
    }

    const contextMenuId = this._button.getAttribute('aria-controls');
    if (null === contextMenuId) {
        throw new Error('Button is not associated with a context-menu element');
    }

    const contextMenu = document.getElementById(contextMenuId);
    if (null === contextMenu) {
        throw new ReferenceError('context-menu element does not exist');
    }

    if (!(contextMenu instanceof ContextMenu)) {
        throw new TypeError('Element is not a ContextMenu element');
    }

    return contextMenu;
}
