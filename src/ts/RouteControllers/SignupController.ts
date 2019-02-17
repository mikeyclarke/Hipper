import { IController } from './IController';
import { SignupForm } from '../onboarding/SignupForm/SignupForm';
import { ElementCache } from 'hleo/ElementCache/ElementCache';
import { EventDelegator } from 'hleo/EventDelegator/EventDelegator';

export class SignupController implements IController {
    private signupForm!: SignupForm;

    public start(): void {
        const signupFormElementCache = new ElementCache('.js-signup-form', SignupForm.elements);
        const signupFormEventDelegator = new EventDelegator(signupFormElementCache.get('form'));
        this.signupForm = new SignupForm(signupFormEventDelegator, signupFormElementCache);
        this.signupForm.init();
    }
}
