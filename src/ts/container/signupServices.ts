import Bottle from 'bottlejs';
import SignUpController from 'SignUpFlow/Controller/SignUpController';
import VerifyEmailAddressController from 'SignUpFlow/Controller/VerifyEmailAddressController';
import ChooseOrganizationUrlController from 'SignUpFlow/Controller/ChooseOrganizationUrlController';
import InvitePeopleController from 'SignUpFlow/Controller/InvitePeopleController';

export default function signupServices(bottle: Bottle): void {
    bottle.factory('signUpController', (container) => {
        return new SignUpController(
            container.formSubmitHelper
        );
    });

    bottle.factory('verifyEmailAddressController', (container) => {
        return new VerifyEmailAddressController(
            container.formSubmitHelper
        );
    });

    bottle.factory('chooseOrganizationUrlController', (container) => {
        return new ChooseOrganizationUrlController(
            container.formSubmitHelper
        );
    });

    bottle.factory('invitePeopleController', (container) => {
        return new InvitePeopleController(
            container.httpClient
        );
    });
}
