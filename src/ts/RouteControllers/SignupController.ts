import { IController } from './IController';
import { SignupForm } from '../onboarding/SignupForm/SignupForm';
import { ElementCache } from 'hleo/ElementCache/ElementCache';
import { EventDelegator } from 'hleo/EventDelegator/EventDelegator';
import { Form } from 'onboarding/Form/Form';

export class SignupController implements IController {
    private signupForm!: SignupForm;

    public start(): void {
        const signupFormElementCache = new ElementCache('.js-signup-form', SignupForm.elements);
        const signupFormEventDelegator = new EventDelegator(signupFormElementCache.get('form'));
        const form = new Form(<HTMLFormElement> signupFormElementCache.get('form'), signupFormElementCache.get('submitButton'));
        this.signupForm = new SignupForm(signupFormEventDelegator, signupFormElementCache, form);
        this.signupForm.init();
    }
}
