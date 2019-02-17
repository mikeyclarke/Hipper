import { IController } from './IController';
import { TeamSubdomainForm } from '../onboarding/TeamSubdomainForm/TeamSubdomainForm';
import { ElementCache } from 'hleo/ElementCache/ElementCache';
import { EventDelegator } from 'hleo/EventDelegator/EventDelegator';

export class TeamSubdomainController implements IController {
    private teamSubdomainForm!: TeamSubdomainForm;

    public start(): void {
        const elementCache = new ElementCache('.js-choose-team-url-form', TeamSubdomainForm.elements);
        const eventDelegator = new EventDelegator(elementCache.get('form'));
        this.teamSubdomainForm = new TeamSubdomainForm(eventDelegator, elementCache);
        this.teamSubdomainForm.init();
    }
}
