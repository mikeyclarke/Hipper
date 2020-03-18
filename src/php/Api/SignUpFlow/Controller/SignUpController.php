<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\SignUpAuthentication\SignUpAuthenticationRequest;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SignUpController
{
    use \Hipper\Api\ApiControllerTrait;

    private SignUpAuthenticationRequest $signUpAuthenticationRequest;

    public function __construct(
        SignUpAuthenticationRequest $signUpAuthenticationRequest
    ) {
        $this->signUpAuthenticationRequest = $signUpAuthenticationRequest;
    }

    public function postAction(Request $request): JsonResponse
    {
        try {
            $authenticationRequest = $this->signUpAuthenticationRequest->create($request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $session = $request->getSession();
        $session->set('_signup_authentication_request_id', $authenticationRequest->getId());

        return new JsonResponse(null, 201);
    }
}
