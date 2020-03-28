import * as Bottle from 'bottlejs';
import SignUpController from 'SignUpFlow/Controller/SignUpController';
import VerifyEmailAddressController from 'SignUpFlow/Controller/VerifyEmailAddressController';
import NameOrganizationController from 'SignUpFlow/Controller/NameOrganizationController';
import ChooseOrganizationUrlController from 'SignUpFlow/Controller/ChooseOrganizationUrlController';

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

    bottle.factory('nameOrganizationController', (container) => {
        return new NameOrganizationController(
            container.formSubmitHelper
        );
    });

    bottle.factory('chooseOrganizationUrlController', (container) => {
        return new ChooseOrganizationUrlController(
            container.formSubmitHelper
        );
    });
}
