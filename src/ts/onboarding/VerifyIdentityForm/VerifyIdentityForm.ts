import EventDelegator from 'hleo/EventDelegator/EventDelegator';
import ElementCache from 'hleo/ElementCache/ElementCache';
import EventsHash from 'hleo/EventDelegator/EventsHash';
import ElementHash from 'hleo/ElementCache/ElementHash';
import EventsEnabled from 'hleo/EventDelegator/EventsEnabled';
import FormValidationErrors from 'onboarding/Form/FormValidationErrors';
import Form from 'onboarding/Form/Form';
import FormSubmitService from 'onboarding/Form/FormSubmitService';

class VerifyIdentityFormData {
    private readonly verififcationCode: string;

    constructor(verififcationCode: string) {
        this.verififcationCode = verififcationCode;
    }

    public get(): object {
        return {
            phrase: this.verififcationCode,
        };
    }
}

export default class VerifyIdentityForm implements EventsEnabled {
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
        form: '.js-verify-identity-form',
        submitButton: '.js-form-submit',
        phraseInputElement: '.js-verification-code-input',
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

    protected onSubmit(event: Event): void {
        event.preventDefault();
        const formData = this.getFormData();
        this.submitService.submit(this.onFormSubmitSuccess.bind(this), this.onFormSubmitFail.bind(this), formData.get());
        this.form.disableSubmitButton();
    }

    protected onFormInteraction(): void {
        this.form.enableSubmitIfFormIsValid();
    }

    public getEvents(): EventsHash {
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
