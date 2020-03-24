<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization\Join;

use Hipper\Api\ApiControllerTrait;
use Hipper\Login\Login;
use Hipper\Person\CreationStrategy\CreateFromApprovedEmailDomain;
use Hipper\SignUpAuthentication\VerifySignUpAuthentication;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VerifyEmailAddressController
{
    use ApiControllerTrait;

    private const ORGANIZATION_HOME_ROUTE_NAME = 'front_end.app.organization.home';

    private CreateFromApprovedEmailDomain $createFromApprovedEmailDomain;
    private Login $login;
    private UrlGeneratorInterface $router;
    private VerifySignUpAuthentication $verifySignUpAuthentication;

    public function __construct(
        CreateFromApprovedEmailDomain $createFromApprovedEmailDomain,
        Login $login,
        UrlGeneratorInterface $router,
        VerifySignUpAuthentication $verifySignUpAuthentication
    ) {
        $this->createFromApprovedEmailDomain = $createFromApprovedEmailDomain;
        $this->login = $login;
        $this->router = $router;
        $this->verifySignUpAuthentication = $verifySignUpAuthentication;
    }

    public function postAction(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('organization');
        $session = $request->getSession();

        $authenticationRequestId = $session->get('_signup_authentication_request_id');
        if (null === $authenticationRequestId) {
            return new JsonResponse(['error_reason' => 'not_found'], 400);
        }

        try {
            $authenticationRequest = $this->verifySignUpAuthentication->verifyWithPhrase(
                $authenticationRequestId,
                $request->request->get('phrase', '')
            );
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        try {
            $person = $this->createFromApprovedEmailDomain->create($organization, $authenticationRequest);
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $this->login->populateSession($session, $person);

        $url = $this->router->generate(self::ORGANIZATION_HOME_ROUTE_NAME, [
            'subdomain' => $organization->getSubdomain(),
        ]);

        return new JsonResponse(['url' => $url], 200);
    }
}
