<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization\Join;

use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class VerifyEmailAddressController
{
    private SignUpAuthenticationRepository $signUpAuthenticationRepository;
    private Twig $twig;

    public function __construct(
        SignUpAuthenticationRepository $signUpAuthenticationRepository,
        Twig $twig
    ) {
        $this->signUpAuthenticationRepository = $signUpAuthenticationRepository;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $session = $request->getSession();
        $organization = $request->attributes->get('organization');

        $authenticationRequestId = $session->get('_signup_authentication_request_id');
        if (null === $authenticationRequestId) {
            return new RedirectResponse('/join');
        }

        $result = $this->signUpAuthenticationRepository->findById($authenticationRequestId);
        if (null === $result) {
            return new RedirectResponse('/join');
        }

        $authenticationRequest = SignUpAuthenticationModel::createFromArray(
            array_merge(['id' => $authenticationRequestId], $result)
        );

        if ($authenticationRequest->getOrganizationId() !== $organization->getId()) {
            return new RedirectResponse('/join');
        }

        $context = [
            'html_title' => 'Verify your email address',
            'email_address' => $authenticationRequest->getEmailAddress(),
        ];

        return new Response(
            $this->twig->render('organization/verify_email_address.twig', $context)
        );
    }
}
