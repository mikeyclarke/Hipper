import { EventDelegator } from '../../hleo/EventDelegator/EventDelegator';
import { ElementCache } from '../../hleo/ElementCache/ElementCache';
import { setTeamSubdomain } from './TeamSubdomainService';
import { IEvents } from '../../hleo/EventDelegator/IEvents';
import { IElementHash } from 'hleo/ElementCache/IElementHash';
import { IEventEnabled } from '../../hleo/EventDelegator/IEventEnabled';
import { TeamSubdomainFormData } from './TeamSubdomainFormData';
import { injectValidationErrors } from 'hleo/FormValidation/ValidationMessageInjector';
import { FormValidationErrors } from 'hleo/FormValidation/FormValidationErrors';

export class TeamSubdomainForm implements IEventEnabled {
    private readonly eventDelegator: EventDelegator;
    private readonly elementCache: ElementCache;

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
        setTeamSubdomain(this.onFormSubmitSuccess.bind(this), this.onFormSubmitFail.bind(this), formData.get());
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
        this.gotoApp();
    }

    private onFormSubmitFail(validationErrors: FormValidationErrors): void {
        Object.entries(validationErrors.violations).forEach(([inputKey, errors]) => {
            injectValidationErrors(this.elementCache.get('form'), inputKey, errors);
        });
    }

    private gotoApp(): void {
        window.location.pathname = '/begin';
    }

    private getFormData(): TeamSubdomainFormData {
        const subdomain = <HTMLInputElement> this.elementCache.get('subdomainInputElement');
        return new TeamSubdomainFormData(subdomain.value);
    }
}
