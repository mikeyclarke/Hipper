import IBootstrap from './IBootstrap';

export default class SignupBootstrap implements IBootstrap
{
    public bootstrap(): void
    {
        require('Sass/signup.scss');
    }
}
