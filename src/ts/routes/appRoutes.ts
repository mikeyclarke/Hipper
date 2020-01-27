import * as Bottle from 'bottlejs';
import RouteDefinition from 'routes/route';

export default function appRoutes(bottle: Bottle): Record<string, RouteDefinition> {
    return {
        login: {
            path: '/login',
            controller: (): any[] => [bottle.container.loginController, 'start'],
        },

        organization_search: {
            path: '/search',
            controller: (): any[] => [bottle.container.searchController, 'start'],
        },

        organization_people_search: {
            path: '/search/people',
            controller: (): any[] => [bottle.container.searchController, 'start'],
        },

        organization_projects_search: {
            path: '/search/projects',
            controller: (): any[] => [bottle.container.searchController, 'start'],
        },

        organization_teams_search: {
            path: '/search/teams',
            controller: (): any[] => [bottle.container.searchController, 'start'],
        },

        team_search: {
            path: '/teams/:team_url_id/search',
            controller: (): any[] => [bottle.container.searchController, 'start'],
        },

        team_members_search: {
            path: '/teams/:team_url_id/search/members',
            controller: (): any[] => [bottle.container.searchController, 'start'],
        },

        project_search: {
            path: '/projects/:project_url_id/search',
            controller: (): any[] => [bottle.container.searchController, 'start'],
        },

        project_members_search: {
            path: '/projects/:project_url_id/search/members',
            controller: (): any[] => [bottle.container.searchController, 'start'],
        },

        create_team: {
            path: '/teams/new',
            controller: (): any[] => [bottle.container.createTeamController, 'start'],
        },

        create_team_doc: {
            path: '/teams/:team_url_id/docs/new',
            controller: (): any[] => [bottle.container.createDocumentController, 'start'],
        },

        create_project: {
            path: '/projects/new',
            controller: (): any[] => [bottle.container.createProjectController, 'start'],
        },

        create_project_doc: {
            path: '/projects/:project_url_id/docs/new',
            controller: (): any[] => [bottle.container.createDocumentController, 'start'],
        },

        create_project_section: {
            path: '/projects/:project_url_id/docs/new-section',
            controller: (): any[] => [bottle.container.createSectionController, 'start'],
        },

        create_team_section: {
            path: '/teams/:team_url_id/docs/new-section',
            controller: (): any[] => [bottle.container.createSectionController, 'start'],
        },

        edit_project_doc: {
            path: '/projects/:project_url_id/docs/edit/:doc_route(.+)',
            controller: (): any[] => [bottle.container.editDocumentController, 'start'],
        },

        edit_team_doc: {
            path: '/teams/:team_url_id/docs/edit/:doc_route(.+)',
            controller: (): any[] => [bottle.container.editDocumentController, 'start'],
        },
    };
}
