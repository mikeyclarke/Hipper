import SignupView from './SignupView';
class SignupController
{
    private view;

    constructor(SignupView: SignupView)
    {
        this.view = SignupView;
    }

    init()
    {
        this.view.init();
    }
}

export default SignupController;
