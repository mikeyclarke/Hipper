import { IController } from './IController';
import { VerifyIdentityForm } from '../onboarding/VerifyIdentityForm/VerifyIdentityForm';
import { ElementCache } from 'hleo/ElementCache/ElementCache';
import { EventDelegator } from 'hleo/EventDelegator/EventDelegator';

export class VerifyIdentityController implements IController {
    private verifyIdentityForm!: VerifyIdentityForm;

    public start(): void {
        const verifyIdentityFormElementCache = new ElementCache('.js-verify-identity-form', VerifyIdentityForm.elements);
        const verifyIdentityFormEventDelegator = new EventDelegator(verifyIdentityFormElementCache.get('form'));
        this.verifyIdentityForm = new VerifyIdentityForm(verifyIdentityFormEventDelegator, verifyIdentityFormElementCache);
        this.verifyIdentityForm.init();
    }
}
