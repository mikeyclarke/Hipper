import { IController } from './IController';
import { NameTeamForm } from '../onboarding/NameTeamForm/NameTeamForm';
import { ElementCache } from 'hleo/ElementCache/ElementCache';
import { EventDelegator } from 'hleo/EventDelegator/EventDelegator';

export class NameTeamController implements IController {
    private nameTeamForm!: NameTeamForm;

    public start(): void {
        const elementCache = new ElementCache('.js-name-team-form', NameTeamForm.elements);
        const eventDelegator = new EventDelegator(elementCache.get('form'));
        this.nameTeamForm = new NameTeamForm(eventDelegator, elementCache);
        this.nameTeamForm.init();
    }
}
