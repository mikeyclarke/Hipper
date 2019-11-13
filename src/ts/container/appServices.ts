import * as Bottle from 'bottlejs';
import LoginController from 'RouteControllers/app/Organization/LoginController';
import CreateTeamController from 'RouteControllers/app/Team/CreateTeamController';
import CreateDocumentController from 'RouteControllers/app/Document/CreateDocumentController';
import CreateProjectController from 'RouteControllers/app/Project/CreateProjectController';
import CreateSectionController from 'RouteControllers/app/Section/CreateSectionController';

export default function (bottle: Bottle): void {
    bottle.factory('loginController', (container) => {
        return new LoginController(
            container.httpClient,
        );
    });

    bottle.factory('createTeamController', (container) => {
        return new CreateTeamController(
            container.httpClient,
        );
    });

    bottle.factory('createDocumentController', (container) => {
        return new CreateDocumentController(
            container.httpClient,
            container.config.user_agent_profile,
        );
    });

    bottle.factory('createProjectController', (container) => {
        return new CreateProjectController(
            container.httpClient,
        );
    });

    bottle.factory('createSectionController', (container) => {
        return new CreateSectionController(
            container.httpClient,
        );
    });
}
