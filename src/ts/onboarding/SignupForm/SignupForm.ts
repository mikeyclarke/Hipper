import EventDelegator from 'hleo/EventDelegator/EventDelegator';
import ElementCache from 'hleo/ElementCache/ElementCache';
import EventsHash from 'hleo/EventDelegator/EventsHash';
import ElementHash from 'hleo/ElementCache/ElementHash';
import EventsEnabled from 'hleo/EventDelegator/EventsEnabled';
import Form from 'onboarding/Form/Form';
import FormValidationErrors from 'onboarding/Form/FormValidationErrors';
import FormSubmitService from 'onboarding/Form/FormSubmitService';

class SignupFormData {
    private readonly name: string;
    private readonly email: string;
    private readonly password: string;
    private readonly termsAgreed: boolean;

    constructor(name: string, email: string, password: string, termsAgreed: boolean) {
        this.name = name;
        this.email = email;
        this.password = password;
        this.termsAgreed = termsAgreed;
    }

    public get(): object {
        return {
            name: this.name,
            email_address: this.email,
            password: this.password,
            terms_agreed: this.termsAgreed,
        };
    }
}

export default class SignupForm {
    private readonly eventDelegator: EventDelegator;
    private readonly elementCache: ElementCache;
    private readonly submitService: FormSubmitService;
    private readonly form: Form;

    private readonly events: EventsHash = {
        keyup: 'onFormInteraction',
        change: 'onFormInteraction',
        submit: 'onSubmit',
    };

    public static readonly elements: ElementHash = {
        form: '.js-signup-form',
        submitButton: '.js-form-submit',
        passwordInputElement: '.js-password-input',
        emailInputElement: '.js-email-input',
        nameInputElement: '.js-name-input',
        termsInputElement: '.js-terms-input',
    };

    constructor(eventDelegator: EventDelegator, elementCache: ElementCache, form: Form, submitService: FormSubmitService) {
        this.eventDelegator = eventDelegator;
        this.elementCache = elementCache;
        this.submitService = submitService;
        this.form = form;
    }

    public init(): void {
        this.eventDelegator.setContext(this);
        this.eventDelegator.delegate();
    }

    public getEvents(): EventsHash {
        return this.events;
    }

    private getFormData(): SignupFormData {
        const passwordEl = <HTMLInputElement> this.elementCache.get('passwordInputElement');
        const emailEl = <HTMLInputElement> this.elementCache.get('emailInputElement');
        const nameEl = <HTMLInputElement> this.elementCache.get('nameInputElement');
        const termsEl = <HTMLInputElement> this.elementCache.get('termsInputElement');
        return new SignupFormData(nameEl.value, emailEl.value, passwordEl.value, termsEl.checked);
    }

    protected onSubmit(event: Event): void {
        event.preventDefault();
        this.form.clearValidationErrors();
        const formData = this.getFormData();
        this.form.disableSubmitButton();
        this.submitService.submit(this.onFormSubmitSuccess.bind(this), this.onFormSubmitFail.bind(this), formData.get());
    }

    private onFormSubmitSuccess(): void {
        this.gotoVerifyIdentityStep();
    }

    private onFormSubmitFail(validationErrors: FormValidationErrors): void {
        this.form.showValidationErrors(validationErrors);
        this.form.enableSubmitButton();
    }

    protected onFormInteraction(): void {
        this.form.enableSubmitIfFormIsValid();
    }

    private gotoVerifyIdentityStep(): void {
        window.location.pathname = '/sign-up/verify-identity';
    }
}
