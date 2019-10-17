import * as Bottle from 'bottlejs';
import RouteDefinition from 'routes/route';

export default function appRoutes(bottle: Bottle): Record<string, RouteDefinition> {
    return {
        create_team: {
            path: '/teams/new',
            controller: () => [bottle.container.createTeamController, 'start'],
        },

        create_team_doc: {
            path: '/teams/:team_url_id/docs/new',
            controller: () => [bottle.container.createDocumentController, 'start'],
        },

        create_project: {
            path: '/projects/new',
            controller: () => [bottle.container.createProjectController, 'start'],
        },

        create_project_doc: {
            path: '/projects/:project_url_id/docs/new',
            controller: () => [bottle.container.createDocumentController, 'start'],
        },

        create_project_section: {
            path: '/projects/:project_url_id/docs/new-section',
            controller: () => [bottle.container.createSectionController, 'start'],
        },

        create_team_section: {
            path: '/teams/:team_url_id/docs/new-section',
            controller: () => [bottle.container.createSectionController, 'start'],
        },
    };
}
