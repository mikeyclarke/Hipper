<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\Api\ApiControllerTrait;
use Hipper\Person\CreationStrategy\CreateFoundingMember;
use Hipper\SignUpAuthentication\VerifySignUpAuthentication;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VerifyEmailAddressController
{
    use ApiControllerTrait;

    private const ORGANIZATION_NAME_ROUTE = 'front_end.sign_up_flow.name_organization';

    private CreateFoundingMember $createFoundingMember;
    private UrlGeneratorInterface $router;
    private VerifySignUpAuthentication $verifySignUpAuthentication;

    public function __construct(
        CreateFoundingMember $createFoundingMember,
        UrlGeneratorInterface $router,
        VerifySignUpAuthentication $verifySignUpAuthentication
    ) {
        $this->createFoundingMember = $createFoundingMember;
        $this->router = $router;
        $this->verifySignUpAuthentication = $verifySignUpAuthentication;
    }

    public function postAction(Request $request): JsonResponse
    {
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
            $person = $this->createFoundingMember->create($authenticationRequest);
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $session->set('_personId', $person->getId());

        $url = $this->router->generate(self::ORGANIZATION_NAME_ROUTE);
        return new JsonResponse(['url' => $url], 200);
    }
}
