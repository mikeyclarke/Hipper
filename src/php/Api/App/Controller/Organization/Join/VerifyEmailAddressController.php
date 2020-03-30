<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization\Join;

use Hipper\Api\ApiControllerTrait;
use Hipper\Login\Login;
use Hipper\SignUp\Exception\EmailAddressAlreadyInUseException;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Hipper\SignUp\SignUpAuthorizationRequestRepository;
use Hipper\SignUp\SignUpStrategy\SignUpFromApprovedEmailDomain;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VerifyEmailAddressController
{
    use ApiControllerTrait;

    private const AUTH_REQUEST_NOT_FOUND_BODY = [
        'name' => 'sign_up_auth_request_not_found',
        'message' => 'Sign-up authorization request not found',
    ];
    private const ORGANIZATION_HOME_ROUTE_NAME = 'front_end.app.organization.home';

    private Login $login;
    private SignUpAuthorizationRequestRepository $signUpAuthorizationRequestRepository;
    private SignUpFromApprovedEmailDomain $signUpFromApprovedEmailDomain;
    private UrlGeneratorInterface $router;

    public function __construct(
        Login $login,
        SignUpAuthorizationRequestRepository $signUpAuthorizationRequestRepository,
        SignUpFromApprovedEmailDomain $signUpFromApprovedEmailDomain,
        UrlGeneratorInterface $router
    ) {
        $this->login = $login;
        $this->signUpAuthorizationRequestRepository = $signUpAuthorizationRequestRepository;
        $this->signUpFromApprovedEmailDomain = $signUpFromApprovedEmailDomain;
        $this->router = $router;
    }

    public function postAction(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('organization');
        $session = $request->getSession();

        $authorizationRequestId = $session->get('_signup_authorization_request_id');
        if (null === $authorizationRequestId) {
            return new JsonResponse(self::AUTH_REQUEST_NOT_FOUND_BODY, 400);
        }

        $result = $this->signUpAuthorizationRequestRepository->findById($authorizationRequestId);
        if (null === $result) {
            return new JsonResponse(self::AUTH_REQUEST_NOT_FOUND_BODY, 400);
        }

        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray(
            array_merge(['id' => $authorizationRequestId], $result)
        );

        try {
            $person = $this->signUpFromApprovedEmailDomain->signUp(
                $authorizationRequest,
                $organization,
                $request->request->all()
            );
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        } catch (EmailAddressAlreadyInUseException $e) {
            return new JsonResponse([
                'name' => 'email_address_taken',
                'message' => 'Email address already in use',
            ], 400);
        }

        $this->login->populateSession($session, $person);

        $url = $this->router->generate(self::ORGANIZATION_HOME_ROUTE_NAME, [
            'subdomain' => $organization->getSubdomain(),
        ]);

        return new JsonResponse(['url' => $url], 200);
    }
}
