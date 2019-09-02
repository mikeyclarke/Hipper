import * as Bottle from 'bottlejs';
import Controller from 'RouteControllers/Controller';
import CreateTeamController from 'RouteControllers/app/Team/CreateTeamController';
import CreateDocumentController from 'RouteControllers/app/Document/CreateDocumentController';
import CreateProjectController from 'RouteControllers/app/Project/CreateProjectController';

export default function (bottle: Bottle): void {
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
}
