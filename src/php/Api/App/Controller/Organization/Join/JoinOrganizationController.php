<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization\Join;

use Hipper\SignUpAuthentication\SignUpAuthenticationRequest;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class JoinOrganizationController
{
    use \Hipper\Api\ApiControllerTrait;

    private const VERIFY_EMAIL_ADDRESS_ROUTE_NAME = 'front_end.app.organization.join.verify_email';

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
        $organization = $request->attributes->get('organization');
        $requestParameters = $this->joinEmailAddress($request->request->all());

        if (!$organization->isApprovedEmailDomainSignupAllowed()) {
            return new JsonResponse(null, 403);
        }

        try {
            $authenticationRequest = $this->signUpAuthenticationRequest->create(
                $requestParameters,
                $organization,
                ['approved_email_domain']
            );
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $session = $request->getSession();
        $session->set('_signup_authentication_request_id', $authenticationRequest->getId());

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
