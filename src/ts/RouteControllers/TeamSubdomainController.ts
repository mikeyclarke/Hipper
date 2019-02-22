import { IController } from './IController';
import { TeamSubdomainForm } from '../onboarding/TeamSubdomainForm/TeamSubdomainForm';
import { ElementCache } from 'hleo/ElementCache/ElementCache';
import { EventDelegator } from 'hleo/EventDelegator/EventDelegator';
import { FormSubmitService } from 'onboarding/Form/FormSubmitService';
import { Form } from 'onboarding/Form/Form';

export class TeamSubdomainController implements IController {
    private teamSubdomainForm!: TeamSubdomainForm;

    public start(): void {
        const elementCache = new ElementCache('.js-choose-team-url-form', TeamSubdomainForm.elements);
        const eventDelegator = new EventDelegator(elementCache.get('form'));
        const form = new Form(<HTMLFormElement> elementCache.get('form'), elementCache.get('submitButton'));
        const submitService = new FormSubmitService(200, '/_/choose-team-url');
        this.teamSubdomainForm = new TeamSubdomainForm(eventDelegator, elementCache, form, submitService);
        this.teamSubdomainForm.init();
    }
}
