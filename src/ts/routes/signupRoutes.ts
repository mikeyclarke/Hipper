import * as Bottle from 'bottlejs';
import RouteDefinition from 'routes/route';

export default function signupRoutes(bottle: Bottle): Record<string, RouteDefinition> {
    return {
        sign_up: {
            path: '/sign-up',
            controller: (): any[] => [bottle.container.signupController, 'start'],
        },

        verify_identity: {
            path: '/sign-up/verify-identity',
            controller: (): any[] => [bottle.container.verifyIdentityController, 'start'],
        },

        name_team: {
            path: '/sign-up/name-team',
            controller: (): any[] => [bottle.container.nameTeamController, 'start'],
        },

        choose_team_url: {
            path: '/sign-up/choose-team-url',
            controller: (): any[] => [bottle.container.teamSubdomainController, 'start'],
        },
    };
}
