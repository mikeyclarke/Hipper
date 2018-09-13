import EventDelegator from '../hleo/EventDelegator/EventDelegator';
import ElementCache from '../hleo/ElementCache/ElementCache';

class SignupForm
{
    private EventDelegator: any;
    private ElementCache: any;
    private isPasswordVisible: boolean = false;
    private eventDelegator: any;
    private elementCache: any;

    private events: object = {
        'keyup': 'onFormInteraction',
        'change': 'onFormInteraction',
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
            this.setTogglePasswordVisibilityText('show');
            this.isPasswordVisible = false;
        } else {
            this.setPasswordFieldType('text');
            this.setTogglePasswordVisibilityText('hide');
            this.isPasswordVisible = true;
        }
        this.elementCache.get('passwordInputElement').focus();
    }

    private setTogglePasswordVisibilityText(text: string): void
    {
        this.elementCache.get('togglePasswordVisibilityButton').innerText = text.toUpperCase();
    }

    private setPasswordFieldType(type: string): void
    {
        this.elementCache.get('passwordInputElement').type = type;
    }
}

export default SignupForm;
