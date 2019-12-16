<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization;

use Hipper\Login\Exception\InvalidCredentialsException;
use Hipper\Login\Login;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LoginController
{
    use \Hipper\Api\ApiControllerTrait;

    private $login;

    public function __construct(
        Login $login
    ) {
        $this->login = $login;
    }

    public function postAction(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('organization');
        $session = $request->getSession();

        try {
            $this->login->login($organization, $request->request->all(), $session);
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

        return new JsonResponse(['url' => '/'], 200);
    }
}
