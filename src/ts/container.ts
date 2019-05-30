import * as Bottle from 'bottlejs';
import { SignupController } from './RouteControllers/onboarding/SignupController';
import { VerifyIdentityController } from './RouteControllers/onboarding/VerifyIdentityController';
import { TeamSubdomainController } from './RouteControllers/onboarding/TeamSubdomainController';
import { NameTeamController } from './RouteControllers/onboarding/NameTeamController';
import { CreateTeamController } from './RouteControllers/app/Team/CreateTeamController';
import { HttpClient } from 'Http/HttpClient';
import { Controller } from 'RouteControllers/Controller';
import { DocumentCookies } from 'Cookie/DocumentCookies';
import { TimeZoneCookie } from 'TimeZone/TimeZoneCookie';
import { TimeZoneRetriever } from 'TimeZone/TimeZoneRetriever';

const bottle = new Bottle();

interface Iroute {
    [key: string]: Controller;
}

bottle.factory('httpClient', (container) => {
    return new HttpClient(
        container.config.csrf_token
    );
});

bottle.factory('documentCookies', () => new DocumentCookies());

bottle.factory('timeZoneRetriever', () => new TimeZoneRetriever());

bottle.factory('timeZoneCookie', (container) => {
    return new TimeZoneCookie(
        container.documentCookies,
        container.timeZoneRetriever
    );
});

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

bottle.factory('createTeamController', (container) => {
    return new CreateTeamController(
        container.httpClient,
    );
});

bottle.factory('bootstrap', (container) => {
    const path: string = window.location.pathname;
    const routes: Iroute = {
        '/sign-up': container.signupController,
        '/verify-identity': container.verifyIdentityController,
        '/name-team': container.nameTeamController,
        '/choose-team-url': container.teamSubdomainController,
        '/teams/new': container.createTeamController,
    };
    if (routes[path]) {
        return routes[path];
    }
    return null;
});

function getCsrfToken(): string | null {
    const metaElement = document.querySelector('.js-csrf');
    if (null === metaElement) {
        return null;
    }

    return metaElement.getAttribute('content');
}

const config = {
    csrf_token: getCsrfToken(),
};

bottle.constant('config', config);

export { bottle };
