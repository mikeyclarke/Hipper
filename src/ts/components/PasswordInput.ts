import { querySelectorNotNull } from 'hleo/QuerySelector/querySelectorNotNull';

export class PasswordInput extends HTMLElement {
    private button!: HTMLElement;
    private input!: HTMLFormElement;

    constructor() {
        super();
    }

    public connectedCallback(): void {
        this.button = querySelectorNotNull(this, 'button');
        this.input = <HTMLFormElement> querySelectorNotNull(this, 'input');
        this.button.addEventListener('click', this.toggleVisibility.bind(this));
    }

    private unmask() {
        this.input.type = 'text';
        this.input.spellcheck = false;
        this.input.autocorrect = 'off';

        this.button.textContent = 'Hide';
    }

    private mask() {
        this.input.type = 'password';
        this.button.textContent = 'Show';
    }

    private toggleVisibility(): void {
        if (this.input.type === 'password') {
            this.unmask();
            return;
        }
        this.mask();
    }
}
