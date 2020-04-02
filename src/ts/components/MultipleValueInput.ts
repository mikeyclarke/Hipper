import ValueAlreadyUsedError from 'components/MultipleValueInput/Error/ValueAlreadyUsedError';
import MaximumValuesInUseError from 'components/MultipleValueInput/Error/MaximumValuesInUseError';
import showFieldError from 'Validation/showFieldError';

const BUTTON_CLASSNAME = 'js-add-value';
const INPUT_CLASSNAME = 'js-value-input';
const LIST_CLASSNAME = 'js-value-list';
const LIST_TEMPLATE_CLASSNAME = 'js-list-template';
const VALUE_TEMPLATE_CLASSNAME = 'js-value-template';
const MAX_VALUES = 20;

function tryAddValueFromInput(this: MultipleValueInput): void {
    const input = this.querySelector(`.${INPUT_CLASSNAME}`);
    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    if (input.value.length === 0) {
        return;
    }

    if (!input.validity.valid) {
        showFieldError(input, input.validationMessage);
        if (document.activeElement !== input) {
            input.focus();
        }
        return;
    }

    try {
        this.addValue(input.value);
        input.value = '';
    } catch (error) {
        if (error instanceof ValueAlreadyUsedError) {
            showFieldError(input, this._duplicateValueMessage);
            if (document.activeElement !== input) {
                input.focus();
            }
            return;
        }

        if (error instanceof MaximumValuesInUseError) {
            showFieldError(input, this._maxValuesMessage);
            if (document.activeElement !== input) {
                input.focus();
            }
            return;
        }

        throw error;
    }
}

function handleClick(this: MultipleValueInput, event: UIEvent): void {
    if (!event.target) {
        return;
    }

    if (!(event.target instanceof HTMLButtonElement)) {
        return;
    }

    if (!event.target.classList.contains(BUTTON_CLASSNAME)) {
        return;
    }

    tryAddValueFromInput.bind(this)();
}

function handleKeydown(this: MultipleValueInput, event: KeyboardEvent): void {
    if (event.key !== 'Enter') {
        return;
    }

    event.preventDefault();

    tryAddValueFromInput.bind(this)();
}

function createList(this: MultipleValueInput): HTMLOListElement {
    const template = <HTMLTemplateElement> this.querySelector(`.${LIST_TEMPLATE_CLASSNAME}`);
    const clone = <HTMLElement> template.content.cloneNode(true);
    const list = <HTMLOListElement> clone.firstElementChild;
    this.appendChild(clone);
    return list;
}

export default class MultipleValueInput extends HTMLElement {
    public _maxValuesMessage: string = 'You’ve added the maximum number of values';
    public _duplicateValueMessage: string = 'You’ve already added that value';
    public _values: string[] = [];
    public _clickHandler: any;
    public _keydownHandler: any;

    constructor() {
        super();

        this._clickHandler = handleClick.bind(this);
        this._keydownHandler = handleKeydown.bind(this);

        if (this.dataset.maxValuesMessage) {
            this._maxValuesMessage = this.dataset.maxValuesMessage;
        }

        if (this.dataset.duplicateValueMessage) {
            this._duplicateValueMessage = this.dataset.duplicateValueMessage;
        }
    }

    public connectedCallback(): void {
        if (!this.isConnected) {
            return;
        }

        this.addEventListener('click', this._clickHandler);
        this.addEventListener('keydown', this._keydownHandler);
    }

    public disconnectedCallback(): void {
        this.removeEventListener('click', this._clickHandler);
        this.removeEventListener('keydown', this._keydownHandler);
    }

    public addValue(value: string): void {
        if (this._values.length >= MAX_VALUES) {
            throw new MaximumValuesInUseError();
        }

        if (this._values.includes(value)) {
            throw new ValueAlreadyUsedError();
        }

        let list = this.querySelector(`.${LIST_CLASSNAME}`);
        if (null === list) {
            list = createList.bind(this)();
        }

        const template = <HTMLTemplateElement> this.querySelector(`.${VALUE_TEMPLATE_CLASSNAME}`);
        const clone = <HTMLElement> template.content.cloneNode(true);

        const input = <HTMLInputElement> clone.querySelector('input');
        input.value = value;
        const span = <HTMLSpanElement> clone.querySelector('span');
        span.textContent = value;

        list.insertBefore(clone, list.firstElementChild);

        this._values.push(value);

        if (this._values.length >= MAX_VALUES) {
            const button = this.querySelector(`.${BUTTON_CLASSNAME}`);
            if (button instanceof HTMLButtonElement) {
                button.disabled = true;
            }
        }
    }
}
