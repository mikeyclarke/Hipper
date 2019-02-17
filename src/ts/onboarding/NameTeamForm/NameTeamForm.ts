import { EventDelegator } from '../../hleo/EventDelegator/EventDelegator';
import { ElementCache } from '../../hleo/ElementCache/ElementCache';
import { nameTeam } from './NameTeamService';
import { IEvents } from '../../hleo/EventDelegator/IEvents';
import { IElementHash } from 'hleo/ElementCache/IElementHash';
import { IEventEnabled } from '../../hleo/EventDelegator/IEventEnabled';
import { NameTeamFormData } from './NameTeamFormData';

export class NameTeamForm implements IEventEnabled {
    private readonly eventDelegator: EventDelegator;
    private readonly elementCache: ElementCache;

    private readonly events: IEvents = {
        keyup: 'onFormInteraction',
        change: 'onFormInteraction',
        submit: 'onSubmit',
    };

    public static readonly elements: IElementHash = {
        form: '.js-name-team-form',
        submitButton: '.js-form-submit',
        nameInputElement: '.js-organization-name-input',
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
        nameTeam(this.onSubmitResponse.bind(this), formData.get());
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
            this.gotoTeamUrlStep();
        }
    }

    private gotoTeamUrlStep(): void {
        window.location.pathname = "/choose-team-url";
    }

    private getFormData(): NameTeamFormData {
        const teamName = <HTMLInputElement> this.elementCache.get('nameInputElement');
        return new NameTeamFormData(teamName.value);
    }
}
