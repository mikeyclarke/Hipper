class SignupView
{
    private isPasswordVisible: boolean = false;
    private form: HTMLFormElement;
    private togglePasswordVisibilityButton: HTMLInputElement;
    private submitButton: HTMLInputElement;
    private passwordInputElement: HTMLInputElement;

    public init(): void
    {
        this.cacheElements();
        this.bindEvents();
    }

    private cacheElements(): void
    {
        this.form = document.querySelector('.js-signup-form');
        this.togglePasswordVisibilityButton = document.querySelector('.js-toggle-password-visibility');
        this.submitButton = document.querySelector('.js-form-submit');
        this.passwordInputElement = document.querySelector('.js-password-input');
    }

    private bindEvents(): void
    {
        this.form.addEventListener('keyup', this.onFormInteraction.bind(this));
        this.form.addEventListener('change', this.onFormInteraction.bind(this));
        this.togglePasswordVisibilityButton.addEventListener('click', this.onTogglePasswordVisibilityClick.bind(this));
    }

    private onFormInteraction(): void
    {
        if (this.form.checkValidity())
        {
            this.submitButton.setAttribute('aria-disabled', 'false');
        } else {
            this.submitButton.setAttribute('aria-disabled', 'true');
        }
    }

    private onTogglePasswordVisibilityClick(): void
    {
        if (this.isPasswordVisible) 
        {
            this.setPasswordFieldType('password');
            this.setTogglePasswordVisibilityText('show');
            this.isPasswordVisible = false;
        } else {
            this.setPasswordFieldType('text');
            this.setTogglePasswordVisibilityText('hide');
            this.isPasswordVisible = true;
        }
    }

    private setTogglePasswordVisibilityText(text: string): void
    {
        this.togglePasswordVisibilityButton.innerText = text.toUpperCase();
    }

    private setPasswordFieldType(type: string): void
    {
        this.passwordInputElement.type = type;
    }
}

export default SignupView;
