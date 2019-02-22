import { EventDelegator } from '../../hleo/EventDelegator/EventDelegator';
import { ElementCache } from '../../hleo/ElementCache/ElementCache';
import { IEvents } from '../../hleo/EventDelegator/IEvents';
import { IElementHash } from 'hleo/ElementCache/IElementHash';
import { IEventEnabled } from '../../hleo/EventDelegator/IEventEnabled';
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

export class TeamSubdomainForm implements IEventEnabled {
    private readonly eventDelegator: EventDelegator;
    private readonly elementCache: ElementCache;
    private readonly form: Form;
    private readonly submitService: FormSubmitService;

    private readonly events: IEvents = {
        keyup: 'onFormInteraction',
        change: 'onFormInteraction',
        submit: 'onSubmit',
    };

    public static readonly elements: IElementHash = {
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

    public getEvents(): IEvents {
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
