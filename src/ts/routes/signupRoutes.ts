import * as Bottle from 'bottlejs';
import RouteDefinition from 'routes/route';

export default function signupRoutes(bottle: Bottle): Record<string, RouteDefinition> {
    return {
        sign_up: {
            path: '/sign-up',
            controller: () => [bottle.container.signupController, 'start'],
        },

        verify_identity: {
            path: '/verify-identity',
            controller: () => [bottle.container.verifyIdentityController, 'start'],
        },

        name_team: {
            path: '/name-team',
            controller: () => [bottle.container.nameTeamController, 'start'],
        },

        choose_team_url: {
            path: '/choose-team-url',
            controller: () => [bottle.container.teamSubdomainController, 'start'],
        },
    };
}
