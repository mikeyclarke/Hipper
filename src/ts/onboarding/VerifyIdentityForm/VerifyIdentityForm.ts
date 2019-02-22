import { EventDelegator } from '../../hleo/EventDelegator/EventDelegator';
import { ElementCache } from '../../hleo/ElementCache/ElementCache';
import { verifyIdentity } from './VerifyIdentityService';
import { IEvents } from '../../hleo/EventDelegator/IEvents';
import { IElementHash } from 'hleo/ElementCache/IElementHash';
import { IEventEnabled } from '../../hleo/EventDelegator/IEventEnabled';
import { VerifyIdentityFormData } from './VerifyIdentityFormData';
import { FormValidationErrors } from 'hleo/FormValidation/FormValidationErrors';
import { injectValidationErrors } from 'hleo/FormValidation/ValidationMessageInjector';
import { Form } from 'onboarding/Form/Form';

export class VerifyIdentityForm implements IEventEnabled {
    private readonly eventDelegator: EventDelegator;
    private readonly elementCache: ElementCache;
    private readonly form: Form;

    private readonly events: IEvents = {
        keyup: 'onFormInteraction',
        change: 'onFormInteraction',
        submit: 'onSubmit',
    };

    public static readonly elements: IElementHash = {
        form: '.js-verify-identity-form',
        submitButton: '.js-form-submit',
        phraseInputElement: '.js-verification-code-input',
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

    protected onSubmit(event: Event): void {
        event.preventDefault();
        const formData = this.getFormData();
        verifyIdentity(this.onFormSubmitSuccess.bind(this), this.onFormSubmitFail.bind(this), formData.get());
        this.form.disableSubmitButton();
    }

    protected onFormInteraction(): void {
        this.form.enableSubmitIfFormIsValid();
    }

    public getEvents(): IEvents {
        return this.events;
    }

    private onFormSubmitSuccess(): void {
        this.gotoNameOrganisationStep();
    }

    private onFormSubmitFail(validationErrors: FormValidationErrors): void {
        this.form.showValidationErrors(validationErrors);
        this.form.enableSubmitButton();
    }

    private gotoNameOrganisationStep(): void {
        window.location.pathname = '/name-team';
    }

    private getFormData(): VerifyIdentityFormData {
        const phraseInputEl = <HTMLInputElement> this.elementCache.get('phraseInputElement');
        return new VerifyIdentityFormData(phraseInputEl.value);
    }
}
