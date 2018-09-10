import IBootstrap from './IBootstrap';
import SignupController from '../Signup/SignupController';

export default class SignupBootstrap implements IBootstrap
{
    private signupController;

    constructor(signupController: SignupController)
    {
        this.signupController = signupController;
    }

    public bootstrap(): void
    {
        this.signupController.init();
    }
}
