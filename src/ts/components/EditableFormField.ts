import { querySelectorNotNull } from 'hleo/QuerySelector/querySelectorNotNull';

export class EditableFormField extends HTMLElement {
    public _editable: boolean;
    public readonly _inputElement: HTMLInputElement | HTMLTextAreaElement;
    public _editButtonElement: HTMLButtonElement | null;

    static get observedAttributes(): string[] {
        return ['editable'];
    }

    constructor() {
        super();

        this._editable = this.hasAttribute('editable');
        this._inputElement = <HTMLInputElement | HTMLTextAreaElement> querySelectorNotNull(this, 'input, textarea');
        this._editButtonElement = <HTMLButtonElement | null> this.querySelector('.js-edit-button');
        addEditButtonClickEvent(this);
    }

    private setInputFocus(): void {
        this._inputElement.focus();
    }

    public set editable(value: boolean) {
        this._editable = value;

        if (value) {
            this.setAttribute('editable', '');
            this._inputElement.readOnly = false;
            this._inputElement.focus();
            this._inputElement.setSelectionRange(0, this._inputElement.value.length);
            removeEditButton(this);
            return;
        }
        this.removeAttribute('editable');
        this._inputElement.readOnly = true;
        createEditButton(this);
    }

    public get editable(): boolean {
        return this._editable;
    }

    public attributeChangedCallback(name: string, oldValue: string, newValue: string): void {
        if (name === 'editable') {
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
                this.editable = value;
            }
        }
    }
}

function addEditButtonClickEvent(element: EditableFormField): void {
    if (null === element._editButtonElement) {
        return;
    }

    element._editButtonElement.addEventListener('click', () => {
        element.editable = true;
    });
}

function removeEditButton(element: EditableFormField): void {
    if (null === element._editButtonElement) {
        return;
    }

    element._editButtonElement.remove();
    element._editButtonElement = null;
}

function createEditButton(element: EditableFormField): void {
    if (null !== element._editButtonElement) {
        return;
    }

    element._editButtonElement = document.createElement('button');
    element._editButtonElement.classList.add('edit-button', 'js-edit-button');
    element._editButtonElement.type = 'button';
    element._editButtonElement.textContent = 'Edit';
    element._editButtonElement.setAttribute('aria-controls', element._inputElement.id);
    addEditButtonClickEvent(element);
    element.appendChild(element._editButtonElement);
}
