import EventDelegator from '../../hleo/EventDelegator/EventDelegator';
import ElementCache from '../../hleo/ElementCache/ElementCache';
import SignupService from './SignupService';

class SignupForm
{
    private isPasswordVisible: boolean = false;
    private eventDelegator: any;
    private elementCache: any;

    private events: object = {
        'keyup': 'onFormInteraction',
        'change': 'onFormInteraction',
        'submit': 'onSubmit',
        'click .js-toggle-password-visibility': 'onTogglePasswordVisibilityClick',
    }

    private elements: object = {
        'form': '.js-signup-form',
        'togglePasswordVisibilityButton': '.js-toggle-password-visibility',
        'submitButton': '.js-form-submit',
        'passwordInputElement': '.js-password-input',
    }

    public init(): void
    {
        this.elementCache = new ElementCache('.js-signup-form', this.elements);
        this.eventDelegator = new EventDelegator(this.events, this.elementCache.get('form'), this);
        this.eventDelegator.delegate();
    }

    protected onSubmit(event): void
    {
        event.preventDefault();
        SignupService.submitForm((res) => {
            console.log(res);
        }, this.getFormData());
    }

    protected onFormInteraction(): void
    {
        if (this.elementCache.get('form').checkValidity())
        {
            this.elementCache.get('submitButton').setAttribute('aria-disabled', 'false');
        } else {
            this.elementCache.get('submitButton').setAttribute('aria-disabled', 'true');
        }
    }

    protected onTogglePasswordVisibilityClick(): void
    {
        if (this.isPasswordVisible) 
        {
            this.setPasswordFieldType('password');
            this.setTogglePasswordVisibilityText('Show');
            this.isPasswordVisible = false;
        } else {
            this.setPasswordFieldType('text');
            this.setTogglePasswordVisibilityText('Hide');
            this.isPasswordVisible = true;
        }
        this.elementCache.get('passwordInputElement').focus();
    }

    private getFormData(): FormData
    {
        return new FormData(this.elementCache.get('form'));
    }

    private setTogglePasswordVisibilityText(text: string): void
    {
        this.elementCache.get('togglePasswordVisibilityButton').innerText = text;
    }

    private setPasswordFieldType(type: string): void
    {
        this.elementCache.get('passwordInputElement').type = type;
    }
}

export default SignupForm;
