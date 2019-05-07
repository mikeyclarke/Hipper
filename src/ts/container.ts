import * as Bottle from 'bottlejs';
import { CKeditor } from './TextEditor/CKEditor/CKEditor';
import { TextEditor } from './TextEditor/TextEditor';
import { IndexController } from './RouteControllers/IndexController';
import { SignupController } from './RouteControllers/onboarding/SignupController';
import { VerifyIdentityController } from './RouteControllers/onboarding/VerifyIdentityController';
import { TeamSubdomainController } from './RouteControllers/onboarding/TeamSubdomainController';
import { NameTeamController } from './RouteControllers/onboarding/NameTeamController';
import { CreateTeamController } from './RouteControllers/app/Team/CreateTeamController';
import { HttpClientFactory } from 'Http/HttpClientFactory';
import { Controller } from 'RouteControllers/Controller';

const bottle = new Bottle();

interface Iroute {
    [key: string]: Controller;
}

bottle.service('httpClientFactory', HttpClientFactory);

bottle.factory('appHttpClient', (container) => {
    const factory = container.httpClientFactory;
    return factory.create();
});

bottle.factory('indexController', (container) => {
    return new IndexController(container.textEditor);
});

bottle.factory('signupController', () => new SignupController());

bottle.factory('verifyIdentityController', () => new VerifyIdentityController());

bottle.factory('nameTeamController', () => new NameTeamController());

bottle.factory('teamSubdomainController', () => new TeamSubdomainController());

bottle.factory('createTeamController', (container) => {
    return new CreateTeamController(
        container.appHttpClient,
    );
});

bottle.factory('CKEditor', () => {
    return new CKeditor();
});

bottle.factory('textEditor', (container) => {
    return new TextEditor(container.CKEditor);
});

bottle.factory('bootstrap', (container) => {
    const path: string = window.location.pathname;
    const routes: Iroute = {
        '/': container.indexController,
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

export { bottle };
