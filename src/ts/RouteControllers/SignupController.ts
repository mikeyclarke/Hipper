import IController from './IController';
import SignupForm from '../onboarding/SignupForm/SignupForm';

export default class SignupController implements IController
{
    private signupForm: SignupForm;

    constructor(SignupForm: SignupForm)
    {
        this.signupForm = SignupForm;
    }

    public start(): void
    {
        this.signupForm.init();
    }
}
