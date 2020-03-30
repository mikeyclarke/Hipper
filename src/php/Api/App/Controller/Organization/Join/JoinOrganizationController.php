<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization\Join;

use Hipper\SignUp\AuthorizationStrategy\ApprovedEmailDomainSignUpAuthorization;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class JoinOrganizationController
{
    use \Hipper\Api\ApiControllerTrait;

    private const VERIFY_EMAIL_ADDRESS_ROUTE_NAME = 'front_end.app.organization.join.verify_email';

    private ApprovedEmailDomainSignUpAuthorization $signUpAuthorization;
    private UrlGeneratorInterface $router;

    public function __construct(
        ApprovedEmailDomainSignUpAuthorization $signUpAuthorization,
        UrlGeneratorInterface $router
    ) {
        $this->signUpAuthorization = $signUpAuthorization;
        $this->router = $router;
    }

    public function postAction(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('organization');
        $requestParameters = $this->joinEmailAddress($request->request->all());

        if (!$organization->isApprovedEmailDomainSignupAllowed()) {
            return new JsonResponse(null, 403);
        }

        try {
            $authorizationRequest = $this->signUpAuthorization->request($organization, $requestParameters);
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $session = $request->getSession();
        $session->set('_signup_authorization_request_id', $authorizationRequest->getId());

        $url = $this->router->generate(self::VERIFY_EMAIL_ADDRESS_ROUTE_NAME, [
            'subdomain' => $organization->getSubdomain(),
        ]);

        return new JsonResponse(['url' => $url], 201);
    }

    private function joinEmailAddress(array $requestParameters): array
    {
        if (isset($requestParameters['email_local_part']) && isset($requestParameters['email_domain'])) {
            $requestParameters['email_address'] = sprintf(
                '%s@%s',
                $requestParameters['email_local_part'],
                $requestParameters['email_domain']
            );
            unset($requestParameters['email_local_part']);
            unset($requestParameters['email_domain']);
        }
        return $requestParameters;
    }
}
