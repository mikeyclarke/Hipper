import { IController } from './IController';
import { VerifyIdentityForm } from '../onboarding/VerifyIdentityForm/VerifyIdentityForm';
import { ElementCache } from 'hleo/ElementCache/ElementCache';
import { EventDelegator } from 'hleo/EventDelegator/EventDelegator';
import { Form } from 'onboarding/Form/Form';

export class VerifyIdentityController implements IController {
    private verifyIdentityForm!: VerifyIdentityForm;

    public start(): void {
        const verifyIdentityFormElementCache = new ElementCache('.js-verify-identity-form', VerifyIdentityForm.elements);
        const verifyIdentityFormEventDelegator = new EventDelegator(verifyIdentityFormElementCache.get('form'));
        const formEl = <HTMLFormElement> verifyIdentityFormElementCache.get('form');
        const submitEl = verifyIdentityFormElementCache.get('submitButton');
        const form = new Form(formEl, submitEl);
        this.verifyIdentityForm = new VerifyIdentityForm(verifyIdentityFormEventDelegator, verifyIdentityFormElementCache, form);
        this.verifyIdentityForm.init();
    }
}
