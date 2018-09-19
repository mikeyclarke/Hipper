import { IController } from './IController';
import SignupForm from '../onboarding/SignupForm/SignupForm';

export class SignupController implements IController {
    private readonly signupForm: SignupForm;

    constructor(signupForm: SignupForm) {
        this.signupForm = signupForm;
    }

    public start(): void {
        this.signupForm.init();
    }
}
