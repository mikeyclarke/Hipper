import timeout from 'Timeout/timeout';

export default class ClearableInput extends HTMLElement {
    public _input: HTMLInputElement | null = null;
    public _clearButton: HTMLButtonElement | null = null;
    public _clickHandler: EventListener | null = null;
    public _focusInHandler: EventListener | null = null;
    public _focusOutHandler: EventListener | null = null;
    public _inputHandler: EventListener | null = null;

    public connectedCallback(): void {
        if (!this.isConnected) {
            return;
        }

        const input = this.querySelector('input');
        const clearButton = this.querySelector('.js-clear-button');

        if (!(input instanceof HTMLInputElement) || !(clearButton instanceof HTMLButtonElement)) {
            return;
        }

        this._input = input;
        this._clearButton = clearButton;

        this._focusInHandler = onFocusIn.bind(this);
        this.addEventListener('focusin', this._focusInHandler);

        this._focusOutHandler = onFocusOut.bind(this);
        this.addEventListener('focusout', this._focusOutHandler);

        this._inputHandler = onInput.bind(this);
        this._input.addEventListener('input', this._inputHandler);

        this._clickHandler = onClearClick.bind(this);
        this._clearButton.addEventListener('click', this._clickHandler);
    }

    public disconnectedCallback(): void {
        if (null !== this._clickHandler && null !== this._clearButton) {
            this._clearButton.removeEventListener('click', this._clickHandler);
            this._clickHandler = null;
        }

        if (null !== this._clearButton) {
            this._clearButton = null;
        }

        if (null !== this._inputHandler && null !== this._input) {
            this._input.removeEventListener('input', this._inputHandler);
            this._inputHandler = null;
        }

        if (null !== this._input) {
            this._input = null;
        }

        if (null !== this._focusInHandler) {
            this.removeEventListener('focusin', this._focusInHandler);
            this._focusInHandler = null;
        }

        if (null !== this._focusOutHandler) {
            this.removeEventListener('focusout', this._focusOutHandler);
            this._focusOutHandler = null;
        }
    }
}

function onClearClick(this: ClearableInput): void {
    if (null === this._input || null === this._clearButton) {
        return;
    }

    this._input.value = '';
    this._input.focus();
    this._clearButton.hidden = true;
}

function onFocusIn(this: ClearableInput): void {
    if (null === this._input || null === this._clearButton) {
        return;
    }

    if (this._input.value.length > 0) {
        this._clearButton.hidden = false;
    }
}

async function onFocusOut(this: ClearableInput): Promise<void> {
    if (null === this._clearButton) {
        return;
    }

    await timeout(150);

    if (null !== document.activeElement && this.contains(document.activeElement)) {
        return;
    }

    this._clearButton.hidden = true;
}

function onInput(this: ClearableInput): void {
    if (null === this._input || null === this._clearButton) {
        return;
    }

    this._clearButton.hidden = (this._input.value.length === 0);
}
