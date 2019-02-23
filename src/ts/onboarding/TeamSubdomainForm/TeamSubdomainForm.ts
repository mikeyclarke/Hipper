import { EventDelegator } from '../../hleo/EventDelegator/EventDelegator';
import { ElementCache } from '../../hleo/ElementCache/ElementCache';
import { EventsHash } from '../../hleo/EventDelegator/EventsHash';
import { ElementHash } from 'hleo/ElementCache/ElementHash';
import { EventsEnabled } from '../../hleo/EventDelegator/EventsEnabled';
import { FormValidationErrors } from 'onboarding/Form/FormValidationErrors';
import { FormSubmitService } from 'onboarding/Form/FormSubmitService';
import { Form } from 'onboarding/Form/Form';

class TeamSubdomainFormData {
    private readonly subdomain: string;

    constructor(subdomain: string) {
        this.subdomain = subdomain;
    }

    public get(): string {
        return JSON.stringify({
            subdomain: this.subdomain,
        });
    }
}

export class TeamSubdomainForm implements EventsEnabled {
    private readonly eventDelegator: EventDelegator;
    private readonly elementCache: ElementCache;
    private readonly form: Form;
    private readonly submitService: FormSubmitService;

    private readonly events: EventsHash = {
        keyup: 'onFormInteraction',
        change: 'onFormInteraction',
        submit: 'onSubmit',
    };

    public static readonly elements: ElementHash = {
        form: '.js-choose-team-url-form',
        submitButton: '.js-form-submit',
        subdomainInputElement: '.js-team-url-input',
    };

    constructor(eventDelegator: EventDelegator, elementCache: ElementCache, form: Form, submitService: FormSubmitService) {
        this.eventDelegator = eventDelegator;
        this.elementCache = elementCache;
        this.form = form;
        this.submitService = submitService;
    }

    public init(): void {
        this.eventDelegator.setContext(this);
        this.eventDelegator.setEvents(this.events);
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
        this.gotoApp();
    }

    private onFormSubmitFail(validationErrors: FormValidationErrors): void {
        this.form.showValidationErrors(validationErrors);
        this.form.enableSubmitButton();
    }

    private gotoApp(): void {
        window.location.pathname = '/begin';
    }

    private getFormData(): TeamSubdomainFormData {
        const subdomain = <HTMLInputElement> this.elementCache.get('subdomainInputElement');
        return new TeamSubdomainFormData(subdomain.value);
    }
}
