import { Controller } from '../Controller';
import { NameTeamForm } from '../../onboarding/NameTeamForm/NameTeamForm';
import { ElementCache } from 'hleo/ElementCache/ElementCache';
import { EventDelegator } from 'hleo/EventDelegator/EventDelegator';
import { Form } from 'onboarding/Form/Form';
import { FormSubmitService } from 'onboarding/Form/FormSubmitService';

export class NameTeamController implements Controller {
    private nameTeamForm!: NameTeamForm;

    public start(): void {
        const elementCache = new ElementCache('.js-name-team-form', NameTeamForm.elements);
        const eventDelegator = new EventDelegator(elementCache.get('form'));
        const form = new Form(<HTMLFormElement> elementCache.get('form'), elementCache.get('submitButton'));
        const submitService = new FormSubmitService(200, '/_/name-team');
        this.nameTeamForm = new NameTeamForm(eventDelegator, elementCache, form, submitService);
        this.nameTeamForm.init();
    }
}
