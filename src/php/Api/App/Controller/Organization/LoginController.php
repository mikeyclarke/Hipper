<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization;

use Hipper\Login\Exception\InvalidCredentialsException;
use Hipper\Login\Login;
use Hipper\Login\LoginSuccessRedirector;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LoginController
{
    use \Hipper\Api\ApiControllerTrait;

    private $login;
    private $loginSuccessRedirector;

    public function __construct(
        Login $login,
        LoginSuccessRedirector $loginSuccessRedirector
    ) {
        $this->login = $login;
        $this->loginSuccessRedirector = $loginSuccessRedirector;
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

        $url = $this->loginSuccessRedirector->generateUri($request);

        return new JsonResponse(['url' => $url], 200);
    }
}
