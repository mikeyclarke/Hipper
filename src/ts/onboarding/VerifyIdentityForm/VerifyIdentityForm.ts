import { EventDelegator } from '../../hleo/EventDelegator/EventDelegator';
import { ElementCache } from '../../hleo/ElementCache/ElementCache';
import { verifyIdentity } from './VerifyIdentityService';
import { IEvents } from '../../hleo/EventDelegator/IEvents';
import { IElementHash } from 'hleo/ElementCache/IElementHash';
import { IEventEnabled } from '../../hleo/EventDelegator/IEventEnabled';
import { VerifyIdentityFormData } from './VerifyIdentityFormData';
import { FormValidationErrors } from 'hleo/FormValidation/FormValidationErrors';
import { injectValidationErrors } from 'hleo/FormValidation/ValidationMessageInjector';

export class VerifyIdentityForm implements IEventEnabled {
    private readonly eventDelegator: EventDelegator;
    private readonly elementCache: ElementCache;

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
        const formData = this.getFormData();
        verifyIdentity(this.onFormSubmitSuccess.bind(this), this.onFormSubmitFail.bind(this), formData.get());
        this.elementCache.get('submitButton').setAttribute('disabled', 'true');
    }

    protected onFormInteraction(): void {
        const form = <HTMLFormElement> this.elementCache.get('form');
        if (form.checkValidity()) {
            this.elementCache.get('submitButton').setAttribute('aria-disabled', 'false');
        } else {
            this.elementCache.get('submitButton').setAttribute('aria-disabled', 'true');
        }
    }

    public getEvents(): IEvents {
        return this.events;
    }

    private onFormSubmitSuccess(): void {
        this.gotoNameOrganisationStep();
    }

    private onFormSubmitFail(validationErrors: FormValidationErrors): void {
        Object.entries(validationErrors.violations).forEach(([inputKey, errors]) => {
            injectValidationErrors(this.elementCache.get('form'), inputKey, errors);
        });
        this.elementCache.get('submitButton').removeAttribute('disabled');
    }

    private gotoNameOrganisationStep(): void {
        window.location.pathname = '/name-team';
    }

    private getFormData(): VerifyIdentityFormData {
        const phraseInputEl = <HTMLInputElement> this.elementCache.get('phraseInputElement');
        return new VerifyIdentityFormData(phraseInputEl.value);
    }
}
