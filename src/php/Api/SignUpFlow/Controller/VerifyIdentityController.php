<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\Api\ApiControllerTrait;
use Hipper\Person\CreationStrategy\CreateFoundingMember;
use Hipper\SignUpAuthentication\Exception\AuthenticationRequestNotFoundException;
use Hipper\SignUpAuthentication\Exception\IncorrectVerificationPhraseException;
use Hipper\SignUpAuthentication\VerifySignUpAuthentication;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class VerifyIdentityController
{
    use ApiControllerTrait;

    private CreateFoundingMember $createFoundingMember;
    private VerifySignUpAuthentication $verifySignUpAuthentication;

    public function __construct(
        CreateFoundingMember $createFoundingMember,
        VerifySignUpAuthentication $verifySignUpAuthentication
    ) {
        $this->createFoundingMember = $createFoundingMember;
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
        } catch (AuthenticationRequestNotFoundException $e) {
            return new JsonResponse(['error_reason' => 'not_found'], 400);
        } catch (IncorrectVerificationPhraseException $e) {
            return new JsonResponse(['error_reason' => 'wrong_phrase'], 400);
        }

        try {
            $person = $this->createFoundingMember->create($authenticationRequest);
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $session->set('_personId', $person->getId());
        return new JsonResponse(null, 200);
    }
}
