<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization\Join;

use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Hipper\SignUp\SignUpAuthorizationRequestRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class VerifyEmailAddressController
{
    private SignUpAuthorizationRequestRepository $signUpAuthorizationRequestRepository;
    private Twig $twig;

    public function __construct(
        SignUpAuthorizationRequestRepository $signUpAuthorizationRequestRepository,
        Twig $twig
    ) {
        $this->signUpAuthorizationRequestRepository = $signUpAuthorizationRequestRepository;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $session = $request->getSession();
        $organization = $request->attributes->get('organization');

        $authorizationRequestId = $session->get('_signup_authorization_request_id');
        if (null === $authorizationRequestId) {
            return new RedirectResponse('/join');
        }

        $result = $this->signUpAuthorizationRequestRepository->findById($authorizationRequestId);
        if (null === $result) {
            return new RedirectResponse('/join');
        }

        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray(
            array_merge(['id' => $authorizationRequestId], $result)
        );

        if ($authorizationRequest->getOrganizationId() !== $organization->getId()) {
            return new RedirectResponse('/join');
        }

        $context = [
            'html_title' => 'Verify your email address',
            'email_address' => $authorizationRequest->getEmailAddress(),
        ];

        return new Response(
            $this->twig->render('organization/verify_email_address.twig', $context)
        );
    }
}
