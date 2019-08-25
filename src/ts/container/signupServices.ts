import * as Bottle from 'bottlejs';
import { Controller } from 'RouteControllers/Controller';
import { SignupController } from 'RouteControllers/onboarding/SignupController';
import { VerifyIdentityController } from 'RouteControllers/onboarding/VerifyIdentityController';
import { TeamSubdomainController } from 'RouteControllers/onboarding/TeamSubdomainController';
import { NameTeamController } from 'RouteControllers/onboarding/NameTeamController';

export const signupServices = function (bottle: Bottle): void {
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
};
