import { EventDelegator } from '../../hleo/EventDelegator/EventDelegator';
import { ElementCache } from '../../hleo/ElementCache/ElementCache';
import { verifyIdentity } from './VerifyIdentityService';
import { IEvents } from '../../hleo/EventDelegator/IEvents';
import { IElementHash } from 'hleo/ElementCache/IElementHash';
import { IEventEnabled } from '../../hleo/EventDelegator/IEventEnabled';
import { VerifyIdentityFormData } from './VerifyIdentityFormData';

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
        verifyIdentity(this.onSubmitResponse.bind(this), formData.get());
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

    private onSubmitResponse(response: Response): void {
        if (response.status === 200) {
            this.gotoNameOrganisationStep();
        }
    }

    private gotoNameOrganisationStep(): void {
        window.location.pathname = "/name-team";
    }

    private getFormData(): VerifyIdentityFormData {
        const phraseInputEl = <HTMLInputElement> this.elementCache.get('phraseInputElement');
        return new VerifyIdentityFormData(phraseInputEl.value);
    }
}
