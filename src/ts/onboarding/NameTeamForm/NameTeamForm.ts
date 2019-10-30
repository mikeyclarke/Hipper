import EventDelegator from 'hleo/EventDelegator/EventDelegator';
import ElementCache from 'hleo/ElementCache/ElementCache';
import EventsHash from 'hleo/EventDelegator/EventsHash';
import ElementHash from 'hleo/ElementCache/ElementHash';
import EventsEnabled from 'hleo/EventDelegator/EventsEnabled';
import FormValidationErrors from 'onboarding/Form/FormValidationErrors';
import Form from 'onboarding/Form/Form';
import FormSubmitService from 'onboarding/Form/FormSubmitService';

class NameTeamFormData {
    private readonly teamName: string;

    constructor(teamName: string) {
        this.teamName = teamName;
    }

    public get(): object {
        return {
            name: this.teamName,
        };
    }
}

export default class NameTeamForm implements EventsEnabled {
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

    private onFormSubmitSuccess(response: Response): void {
        this.gotoTeamUrlStep();
    }

    private onFormSubmitFail(validationErrors: FormValidationErrors): void {
        this.form.showValidationErrors(validationErrors);
        this.form.enableSubmitButton();
    }

    private gotoTeamUrlStep(): void {
        window.location.pathname = '/sign-up/choose-team-url';
    }

    private getFormData(): NameTeamFormData {
        const teamName = <HTMLInputElement> this.elementCache.get('nameInputElement');
        return new NameTeamFormData(teamName.value);
    }
}
