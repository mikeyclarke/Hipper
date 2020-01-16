<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization;

use Hipper\Login\Exception\InvalidCredentialsException;
use Hipper\Login\Login;
use Hipper\Security\UntrustedInternalUriRedirector;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LoginController
{
    use \Hipper\Api\ApiControllerTrait;

    private $login;
    private $untrustedInternalUriRedirector;

    public function __construct(
        Login $login,
        UntrustedInternalUriRedirector $untrustedInternalUriRedirector
    ) {
        $this->login = $login;
        $this->untrustedInternalUriRedirector = $untrustedInternalUriRedirector;
    }

    public function postAction(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('organization');
        $session = $request->getSession();

        $requestBody = array_diff_key($request->request->all(), array_flip(['redirect']));

        try {
            $this->login->login($organization, $requestBody, $session);
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        } catch (InvalidCredentialsException $e) {
            return new JsonResponse(
                [
                    'name' => 'invalid_credentials',
                    'message' => 'We couldn’t sign you in. Check your email address and password',
                ],
                400
            );
        }

        $url = $this->untrustedInternalUriRedirector->generateUri($request->request->get('redirect'), '/');

        return new JsonResponse(['url' => $url], 200);
    }
}
