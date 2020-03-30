<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\Api\ApiControllerTrait;
use Hipper\SignUp\Exception\EmailAddressAlreadyInUseException;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Hipper\SignUp\SignUpAuthorizationRequestRepository;
use Hipper\SignUp\SignUpStrategy\SignUpFoundingMember;
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
    private const ORGANIZATION_URL_ROUTE = 'front_end.sign_up_flow.choose_organization_url';

    private SignUpAuthorizationRequestRepository $signUpAuthorizationRequestRepository;
    private SignUpFoundingMember $signUpFoundingMember;
    private UrlGeneratorInterface $router;

    public function __construct(
        SignUpAuthorizationRequestRepository $signUpAuthorizationRequestRepository,
        SignUpFoundingMember $signUpFoundingMember,
        UrlGeneratorInterface $router
    ) {
        $this->signUpAuthorizationRequestRepository = $signUpAuthorizationRequestRepository;
        $this->signUpFoundingMember = $signUpFoundingMember;
        $this->router = $router;
    }

    public function postAction(Request $request): JsonResponse
    {
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
            $person = $this->signUpFoundingMember->signUp($authorizationRequest, $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        } catch (EmailAddressAlreadyInUseException $e) {
            return new JsonResponse([
                'name' => 'email_address_taken',
                'message' => 'Email address already in use',
            ], 400);
        }

        $session->set('_personId', $person->getId());

        $url = $this->router->generate(self::ORGANIZATION_URL_ROUTE);
        return new JsonResponse(['url' => $url], 200);
    }
}
