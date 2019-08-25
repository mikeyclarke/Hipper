import * as Bottle from 'bottlejs';
import RouteDefinition from 'routes/route';

export default function signupRoutes(bottle: Bottle): Record<string, RouteDefinition> {
    return {
        sign_up: {
            path: '/sign-up',
            controller: () => bottle.container.signupController,
        },

        verify_identity: {
            path: '/verify-identity',
            controller: () => bottle.container.verifyIdentityController,
        },

        name_team: {
            path: '/name-team',
            controller: () => bottle.container.nameTeamController,
        },

        choose_team_url: {
            path: '/choose-team-url',
            controller: () => bottle.container.teamSubdomainController,
        }
    };
}
