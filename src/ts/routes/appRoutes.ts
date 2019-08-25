import * as Bottle from 'bottlejs';
import { RouteDefinition } from 'routes/route';

export const appRoutes = function (bottle: Bottle): Record<string, RouteDefinition> {
    return {
        create_team: {
            path: '/teams/new',
            controller: () => bottle.container.createTeamController,
        },

        create_team_doc: {
            path: '/team/engineering/docs/new',
            controller: () => bottle.container.createTeamDocController,
        },

        create_project: {
            path: '/projects/new',
            controller: () => bottle.container.createProjectController,
        },
    };
};
