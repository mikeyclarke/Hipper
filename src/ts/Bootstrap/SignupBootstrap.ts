import IBootstrap from './IBootstrap';
import SignupForm from '../SignupForm/SignupForm';

export default class SignupBootstrap implements IBootstrap
{
    private signupForm: SignupForm;

    constructor(SignupForm: SignupForm)
    {
        this.signupForm = SignupForm;
    }

    public bootstrap(): void
    {
        this.signupForm.init();
    }
}
