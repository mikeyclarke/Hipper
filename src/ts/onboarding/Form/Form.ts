import { injectValidationErrors } from 'hleo/FormValidation/ValidationMessageInjector';
import { FormValidationErrors } from 'hleo/FormValidation/FormValidationErrors';

export class Form {
    private readonly form: HTMLFormElement;
    private readonly submitButton: HTMLElement;

    constructor(form: HTMLFormElement, submitButton: HTMLElement) {
        this.form = form;
        this.submitButton = submitButton;
    }

    public enableSubmitIfFormIsValid(): void {
        if (this.form.checkValidity()) {
            this.enableSubmitButton();
        } else {
            this.disableSubmitButton();
        }
    }

    public showValidationErrors(validationErrors: FormValidationErrors): void {
        Object.entries(validationErrors.violations).forEach(([inputKey, errors]) => {
            injectValidationErrors(this.form, inputKey, errors);
        });
    }

    public disableSubmitButton(): void {
        this.submitButton.setAttribute('aria-disabled', 'true');
    }

    public enableSubmitButton(): void {
        this.submitButton.setAttribute('aria-disabled', 'false');
    }

    public setFormSubmittingState(): void {
        this.clearValidationErrors();
        this.disableSubmitButton();
    }

    public clearValidationErrors(): void {
        this.form.querySelectorAll('.js-form-error').forEach((el: Element) => {
            el.remove();
        });

        this.form.querySelectorAll('.js-form-input').forEach((el: Element) => {
            el.classList.remove('validation-error');
        });
    }
}