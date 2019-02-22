import { EventDelegator } from '../../hleo/EventDelegator/EventDelegator';
import { ElementCache } from '../../hleo/ElementCache/ElementCache';
import { IEvents } from '../../hleo/EventDelegator/IEvents';
import { IElementHash } from 'hleo/ElementCache/IElementHash';
import { IEventEnabled } from '../../hleo/EventDelegator/IEventEnabled';
import { FormValidationErrors } from 'onboarding/Form/FormValidationErrors';
import { Form } from 'onboarding/Form/Form';
import { FormSubmitService } from 'onboarding/Form/FormSubmitService';

class NameTeamFormData {
    private readonly teamName: string;

    constructor(teamName: string) {
        this.teamName = teamName;
    }

    public get(): string {
        return JSON.stringify({
            name: this.teamName,
        });
    }
}

export class NameTeamForm implements IEventEnabled {
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
        form: '.js-name-team-form',
        submitButton: '.js-form-submit',
        nameInputElement: '.js-organization-name-input',
    };

    constructor(eventDelegator: EventDelegator, elementCache: ElementCache, form: Form, submitService: FormSubmitService) {
        this.eventDelegator = eventDelegator;
        this.elementCache = elementCache;
        this.submitService = submitService;
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
        this.submitService.submit(this.onFormSubmitSuccess.bind(this), this.onFormSubmitFail.bind(this), formData.get());
        this.form.disableSubmitButton();
    }

    protected onFormInteraction(): void {
        this.form.enableSubmitIfFormIsValid();
    }

    public getEvents(): IEvents {
        return this.events;
    }

    private onFormSubmitSuccess(response: Response): void {
        this.gotoTeamUrlStep();
    }

    private onFormSubmitFail(validationErrors: FormValidationErrors): void {
        this.form.showValidationErrors(validationErrors);
        this.form.enableSubmitButton();
    }

    private gotoTeamUrlStep(): void {
        window.location.pathname = '/choose-team-url';
    }

    private getFormData(): NameTeamFormData {
        const teamName = <HTMLInputElement> this.elementCache.get('nameInputElement');
        return new NameTeamFormData(teamName.value);
    }
}
