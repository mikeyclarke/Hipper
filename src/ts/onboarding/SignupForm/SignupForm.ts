import { EventDelegator } from '../../hleo/EventDelegator/EventDelegator';
import { ElementCache } from '../../hleo/ElementCache/ElementCache';
import { submitSignup } from './SignupService';
import { IEvents } from '../../hleo/EventDelegator/IEvents';
import { IElementHash } from 'hleo/ElementCache/IElementHash';
import { IEventEnabled } from '../../hleo/EventDelegator/IEventEnabled';
import { SignupFormData } from './SignupFormData';
import { Form } from 'onboarding/Form/Form';
import { FormValidationErrors } from 'hleo/FormValidation/FormValidationErrors';

export class SignupForm implements IEventEnabled {
    private isPasswordVisible: boolean = false;
    private readonly eventDelegator: EventDelegator;
    private readonly elementCache: ElementCache;
    private readonly form: Form;

    private readonly events: IEvents = {
        keyup: 'onFormInteraction',
        change: 'onFormInteraction',
        submit: 'onSubmit',
        'click .js-toggle-password-visibility': 'onTogglePasswordVisibilityClick',
    };

    public static readonly elements: IElementHash = {
        form: '.js-signup-form',
        togglePasswordVisibilityButton: '.js-toggle-password-visibility',
        submitButton: '.js-form-submit',
        passwordInputElement: '.js-password-input',
        emailInputElement: '.js-email-input',
        nameInputElement: '.js-name-input',
        termsInputElement: '.js-terms-input',
    };

    constructor(eventDelegator: EventDelegator, elementCache: ElementCache, form: Form) {
        this.eventDelegator = eventDelegator;
        this.elementCache = elementCache;
        this.form = form;
    }

    public init(): void {
        this.eventDelegator.setContext(this);
        this.eventDelegator.setEvents(this.events);
        this.eventDelegator.delegate();
    }

    public getEvents(): IEvents {
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
        submitSignup(this.onFormSubmitSuccess.bind(this), this.onFormSubmitFail.bind(this), formData.get());
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

    protected onTogglePasswordVisibilityClick(): void {
        if (this.isPasswordVisible) {
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

    private setTogglePasswordVisibilityText(text: string): void {
        this.elementCache.get('togglePasswordVisibilityButton').innerText = text;
    }

    private setPasswordFieldType(type: string): void {
        const passwordField = <HTMLInputElement> this.elementCache.get('passwordInputElement');
        passwordField.type = type;
    }

    private gotoVerifyIdentityStep(): void {
        window.location.pathname = '/verify-identity';
    }
}
