import { Controller } from '../Controller';
import { VerifyIdentityForm } from '../../onboarding/VerifyIdentityForm/VerifyIdentityForm';
import { ElementCache } from 'hleo/ElementCache/ElementCache';
import { EventDelegator } from 'hleo/EventDelegator/EventDelegator';
import { Form } from 'onboarding/Form/Form';
import { FormSubmitService } from 'onboarding/Form/FormSubmitService';

export class VerifyIdentityController implements Controller {
    private verifyIdentityForm!: VerifyIdentityForm;

    public start(): void {
        const elementCache = new ElementCache('.js-verify-identity-form', VerifyIdentityForm.elements);
        const eventDelegator = new EventDelegator(elementCache.get('form'));
        const formEl = <HTMLFormElement> elementCache.get('form');
        const submitEl = elementCache.get('submitButton');
        const submitService = new FormSubmitService(200, '/_/verify-identity');
        const form = new Form(formEl, submitEl);
        this.verifyIdentityForm = new VerifyIdentityForm(eventDelegator, elementCache, form, submitService);
        this.verifyIdentityForm.init();
    }
}
