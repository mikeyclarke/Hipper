<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\SignUpAuthentication\SignUpAuthenticationRequest;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SignUpController
{
    use \Hipper\Api\ApiControllerTrait;

    private const VERIFY_EMAIL_ROUTE = 'front_end.sign_up_flow.verify_email_address';

    private SignUpAuthenticationRequest $signUpAuthenticationRequest;
    private UrlGeneratorInterface $router;

    public function __construct(
        SignUpAuthenticationRequest $signUpAuthenticationRequest,
        UrlGeneratorInterface $router
    ) {
        $this->signUpAuthenticationRequest = $signUpAuthenticationRequest;
        $this->router = $router;
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

        $url = $this->router->generate(self::VERIFY_EMAIL_ROUTE);
        return new JsonResponse(['url' => $url], 201);
    }
}
