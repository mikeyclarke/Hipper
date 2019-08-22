import * as Bottle from 'bottlejs';
import { SignupController } from './RouteControllers/onboarding/SignupController';
import { VerifyIdentityController } from './RouteControllers/onboarding/VerifyIdentityController';
import { TeamSubdomainController } from './RouteControllers/onboarding/TeamSubdomainController';
import { NameTeamController } from './RouteControllers/onboarding/NameTeamController';
import { CreateTeamController } from './RouteControllers/app/Team/CreateTeamController';
import { CreateTeamDocController } from './RouteControllers/app/Team/CreateTeamDocController';
import { CreateProjectController } from 'RouteControllers/app/Project/CreateProjectController';
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

bottle.factory('createTeamDocController', (container) => {
    return new CreateTeamDocController(
        container.httpClient,
        container.config.user_agent_profile,
    );
});

bottle.factory('createProjectController', (container) => {
    return new CreateProjectController(
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
        '/team/engineering/docs/new': container.createTeamDocController,
        '/projects/new': container.createProjectController,
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

function getUserAgentProfile(): Record<string, any> | null {
    const metaElement = document.head.querySelector('.js-user-agent-profile');
    if (null === metaElement) {
        return null;
    }

    const profile = metaElement.getAttribute('content');
    if (null === profile) {
        return null;
    }

    return JSON.parse(profile);
}

const config = {
    csrf_token: getCsrfToken(),
    user_agent_profile: getUserAgentProfile(),
};

bottle.constant('config', config);

export { bottle };
