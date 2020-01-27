import * as Bottle from 'bottlejs';
import SignupController from 'RouteControllers/onboarding/SignupController';
import VerifyIdentityController from 'RouteControllers/onboarding/VerifyIdentityController';
import TeamSubdomainController from 'RouteControllers/onboarding/TeamSubdomainController';
import NameTeamController from 'RouteControllers/onboarding/NameTeamController';

export default function signupServices(bottle: Bottle): void {
    bottle.factory('signupController', (container) => {
        return new SignupController(
            container.httpClient
        );
    });

    bottle.factory('verifyIdentityController', (container) => {
        return new VerifyIdentityController(
            container.httpClient
        );
    });

    bottle.factory('nameTeamController', (container) => {
        return new NameTeamController(
            container.httpClient
        );
    });

    bottle.factory('teamSubdomainController', (container) => {
        return new TeamSubdomainController(
            container.httpClient
        );
    });
}
