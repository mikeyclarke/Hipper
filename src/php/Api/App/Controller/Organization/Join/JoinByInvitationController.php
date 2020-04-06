<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization\Join;

use Hipper\Login\Login;
use Hipper\SignUp\Exception\EmailAddressAlreadyInUseException;
use Hipper\SignUp\Exception\InviteExpiredException;
use Hipper\SignUp\Exception\InviteNotFoundException;
use Hipper\SignUp\SignUpStrategy\SignUpFromInvitation;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class JoinByInvitationController
{
    use \Hipper\Api\ApiControllerTrait;

    private const ORGANIZATION_HOME_ROUTE_NAME = 'front_end.app.organization.home';

    private Login $login;
    private SignUpFromInvitation $signUpFromInvitation;
    private UrlGeneratorInterface $router;

    public function __construct(
        Login $login,
        SignUpFromInvitation $signUpFromInvitation,
        UrlGeneratorInterface $router
    ) {
        $this->login = $login;
        $this->signUpFromInvitation = $signUpFromInvitation;
        $this->router = $router;
    }

    public function postAction(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        try {
            $person = $this->signUpFromInvitation->signUp($organization, $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        } catch (InviteNotFoundException $e) {
            return new JsonResponse([
                'name' => 'invite_not_found',
                'message' => 'Invite not found',
            ], 400);
        } catch (InviteExpiredException $e) {
            return new JsonResponse([
                'name' => 'invite_expired',
                'message' => 'Invite expired',
            ], 400);
        } catch (EmailAddressAlreadyInUseException $e) {
            return new JsonResponse([
                'name' => 'email_address_taken',
                'message' => 'Email address already in use',
            ], 400);
        }

        $session = $request->getSession();
        $this->login->populateSession($session, $person);

        $url = $this->router->generate(self::ORGANIZATION_HOME_ROUTE_NAME, [
            'subdomain' => $organization->getSubdomain(),
        ]);

        return new JsonResponse(['url' => $url], 201);
    }
}
