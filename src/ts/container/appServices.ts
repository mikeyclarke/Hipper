import * as Bottle from 'bottlejs';
import Controller from 'RouteControllers/Controller';
import CreateTeamController from 'RouteControllers/app/Team/CreateTeamController';
import CreateTeamDocController from 'RouteControllers/app/Team/CreateTeamDocController';
import CreateProjectController from 'RouteControllers/app/Project/CreateProjectController';

export default function (bottle: Bottle): void {
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
}
