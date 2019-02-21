import { EventDelegator } from '../../hleo/EventDelegator/EventDelegator';
import { ElementCache } from '../../hleo/ElementCache/ElementCache';
import { submitSignup } from './SignupService';
import { IEvents } from '../../hleo/EventDelegator/IEvents';
import { IElementHash } from 'hleo/ElementCache/IElementHash';
import { IEventEnabled } from '../../hleo/EventDelegator/IEventEnabled';
import { SignupFormData } from './SignupFormData';
import { FormValidationErrors } from 'hleo/FormValidation/FormValidationErrors';

export class SignupForm implements IEventEnabled {
    private isPasswordVisible: boolean = false;
    private readonly eventDelegator: EventDelegator;
    private readonly elementCache: ElementCache;

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

    constructor(eventDelegator: EventDelegator, elementCache: ElementCache) {
        this.eventDelegator = eventDelegator;
        this.elementCache = elementCache;
    }

    public init(): void {
        this.eventDelegator.setContext(this);
        this.eventDelegator.setEvents(this.events);
        this.eventDelegator.delegate();
    }

    protected onSubmit(event: Event): void {
        event.preventDefault();
        this.clearValidationErrors();
        const formData = this.getFormData();
        submitSignup(this.onFormSubmitSuccess.bind(this), this.onFormSubmitFail.bind(this), formData.get());
    }

    public getEvents(): IEvents {
        return this.events;
    }

    protected onFormInteraction(): void {
        const form = <HTMLFormElement> this.elementCache.get('form');
        if (form.checkValidity()) {
            this.elementCache.get('submitButton').setAttribute('aria-disabled', 'false');
        } else {
            this.elementCache.get('submitButton').setAttribute('aria-disabled', 'true');
        }
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

    private onFormSubmitSuccess(): void {
        this.gotoVerifyIdentityStep();
    }

    private onFormSubmitFail(validationErrors: FormValidationErrors): void {
        Object.entries(validationErrors.violations).forEach(([key, value]) => {
            this.injectValidationErrors(key, value);
        });
    }

    private injectValidationErrors(target: string, errors: string[]): void {
        const containerSelector = `[data-validation-error-container=${target}`;
        const validationContainerEl = <HTMLElement> this.elementCache.get('form').querySelector(containerSelector);
        errors.forEach((error: string) => {
            const validationError = this.createValidationErrorElement(error);
            validationContainerEl.appendChild(validationError);
        });
    }

    private createValidationErrorElement(error: string): HTMLElement {
        const newErrorEl = document.createElement('p');
        newErrorEl.classList.add('c-signup-form__field-error', 'js-form-error');
        newErrorEl.innerText = error;
        return newErrorEl;
    }

    private clearValidationErrors(): void {
        this.elementCache.get('form').querySelectorAll('.js-form-error').forEach((el: Element) => {
            el.remove();
        });
    }

    private gotoVerifyIdentityStep(): void {
        window.location.pathname = '/verify-identity';
    }

    private getFormData(): SignupFormData {
        const passwordEl = <HTMLInputElement> this.elementCache.get('passwordInputElement');
        const emailEl = <HTMLInputElement> this.elementCache.get('emailInputElement');
        const nameEl = <HTMLInputElement> this.elementCache.get('nameInputElement');
        const termsEl = <HTMLInputElement> this.elementCache.get('termsInputElement');
        return new SignupFormData(nameEl.value, emailEl.value, passwordEl.value, termsEl.checked);
    }

    private setTogglePasswordVisibilityText(text: string): void {
        this.elementCache.get('togglePasswordVisibilityButton').innerText = text;
    }

    private setPasswordFieldType(type: string): void {
        const passwordField = <HTMLInputElement> this.elementCache.get('passwordInputElement');
        passwordField.type = type;
    }
}
