const supportedTypes = ['error'];

export class PopoverAlert extends HTMLElement {
    public _containerElement!: HTMLDivElement;
    public _buttonElement!: HTMLButtonElement;
    public _closeTimeoutId!: number;
    public _documentVisibilityListener!: EventListener;

    constructor() {
        super();
    }

    public connectedCallback(): void {
        if (!this.isConnected || this._containerElement) {
            return;
        }

        if (null === this.getAttribute('alert-type')) {
            throw new Error('<popover-alert> `alert-type` attribute required but not provided');
        }

        const alertType = <string> this.getAttribute('alert-type');
        if (!supportedTypes.includes(alertType)) {
            throw new Error('<popover-alert> `alert-type` attribute value is invalid');
        }

        this.classList.add(alertType);
        this.setAttribute('role', 'alert');

        createContainer(this);
        createTitle(this);
        createMessage(this);
        createCloseButton(this);

        if (document.hidden) {
            this._documentVisibilityListener = onDocumentVisibility.bind(null, this);
            document.addEventListener('visibilitychange', this._documentVisibilityListener);
            return;
        }

        setUpClose(this);
    }
}

function close(element: PopoverAlert, force: boolean = false): void {
    if (element._containerElement.matches(':hover') && !force) {
        element._containerElement.addEventListener('mouseleave', close.bind(null, element, false));
        return;
    }

    element._containerElement.addEventListener('transitionend', () => {
        element.remove();
    });
    element._containerElement.classList.add('is-hiding');
}

function handleCloseButtonClicked(element: PopoverAlert): void {
    window.clearTimeout(element._closeTimeoutId);
    close(element, true);
}

function setUpClose(element: PopoverAlert): void {
    element._buttonElement.addEventListener('click', handleCloseButtonClicked.bind(null, element));
    element._closeTimeoutId = window.setTimeout(close.bind(null, element), 8000);
}

function onDocumentVisibility(element: PopoverAlert): void {
    document.removeEventListener('visibilitychange', element._documentVisibilityListener);
    setUpClose(element);
}

function createCloseButton(element: PopoverAlert): void {
    const button = document.createElement('button');
    button.type = 'button';
    button.classList.add('close-button');
    button.setAttribute('aria-label', 'Close');
    button.innerHTML =
        '<svg class="close-button-icon" aria-hidden="true"><use xlink:href="#icon-sprite__close"/></svg>';

    element._buttonElement = button;
    element._containerElement.appendChild(button);
}

function createMessage(element: PopoverAlert): void {
    const message = document.createElement('p');
    message.classList.add('message');
    message.textContent = element.getAttribute('alert-message');
    element._containerElement.appendChild(message);
}

function createTitle(element: PopoverAlert): void {
    const title = document.createElement('h1');
    title.classList.add('title');
    title.textContent = element.getAttribute('alert-title');
    element._containerElement.appendChild(title);
}

function createContainer(element: PopoverAlert): void {
    const container = document.createElement('div');
    container.classList.add('container');
    element._containerElement = container;
    element.appendChild(container);
}
