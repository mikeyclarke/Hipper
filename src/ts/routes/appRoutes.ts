import * as Bottle from 'bottlejs';
import RouteDefinition from 'routes/route';

export default function appRoutes(bottle: Bottle): Record<string, RouteDefinition> {
    return {
        create_team: {
            path: '/teams/new',
            controller: () => [bottle.container.createTeamController, 'start'],
        },

        create_team_doc: {
            path: '/team/:team_url_id/docs/new',
            controller: () => [bottle.container.createTeamDocController, 'start'],
        },

        create_project: {
            path: '/projects/new',
            controller: () => [bottle.container.createProjectController, 'start'],
        },
    };
}
