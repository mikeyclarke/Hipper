import * as Bottle from 'bottlejs';
import RouteDefinition from 'routes/route';

export default function signupRoutes(bottle: Bottle): Record<string, RouteDefinition> {
    return {
        sign_up: {
            path: '/sign-up',
            controller: (): any[] => [bottle.container.signUpController, 'start'],
        },

        verify_email_address: {
            path: '/sign-up/verify-email-address',
            controller: (): any[] => [bottle.container.verifyEmailAddressController, 'start'],
        },

        choose_organization_url: {
            path: '/sign-up/choose-organization-url',
            controller: (): any[] => [bottle.container.chooseOrganizationUrlController, 'start'],
        },
    };
}
