import IBootstrap from './IBootstrap';
import SignupView from '../Signup/SignupView';

export default class SignupBootstrap implements IBootstrap
{
    private signupView;

    constructor(SignupView: SignupView)
    {
        this.signupView = SignupView;
    }

    public bootstrap(): void
    {
        this.signupView.init();
    }
}
