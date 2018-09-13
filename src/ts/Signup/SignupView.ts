class SignupView
{
    private EventDelegator: any;
    private ElementCache: any;
    private isPasswordVisible: boolean = false;
    private eventDelegator: any;
    private dom: any;

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

    constructor(EventDelegator: any, ElementCache: any)
    {
        this.EventDelegator = EventDelegator;
        this.ElementCache = ElementCache;
    }

    public init(): void
    {
        this.dom = new this.ElementCache('.js-signup-form', this.elements);
        this.eventDelegator = new this.EventDelegator(this.events, this.dom.get('form'), this);
        this.eventDelegator.bindEvents();
    }

    protected onFormInteraction(): void
    {
        if (this.dom.get('form').checkValidity())
        {
            this.dom.get('submitButton').setAttribute('aria-disabled', 'false');
        } else {
            this.dom.get('submitButton').setAttribute('aria-disabled', 'true');
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
    }

    private setTogglePasswordVisibilityText(text: string): void
    {
        this.dom.get('togglePasswordVisibilityButton').innerText = text.toUpperCase();
    }

    private setPasswordFieldType(type: string): void
    {
        this.dom.get('passwordInputElement').type = type;
    }
}

export default SignupView;
