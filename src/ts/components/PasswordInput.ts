export default class PasswordInput extends HTMLElement {
    private button!: HTMLButtonElement;
    private input!: HTMLInputElement;

    public connectedCallback(): void {
        const button = this.querySelector('button');
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }
        this.button = button;

        const input = this.querySelector('input');
        if (!(input instanceof HTMLInputElement)) {
            return;
        }
        this.input = input;

        this.button.addEventListener('click', this.toggleVisibility.bind(this));
    }

    private unmask(): void {
        this.input.type = 'text';
        this.input.spellcheck = false;
        this.input.setAttribute('autocorrect', 'off');

        this.button.textContent = 'Hide';

        this.input.focus();
    }

    private mask(): void {
        this.input.type = 'password';
        this.button.textContent = 'Show';

        this.input.focus();
    }

    private toggleVisibility(): void {
        if (this.input.type === 'password') {
            this.unmask();
            return;
        }
        this.mask();
    }
}
