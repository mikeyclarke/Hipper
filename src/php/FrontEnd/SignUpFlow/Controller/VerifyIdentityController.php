<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\SignUpFlow\Controller;

use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class VerifyIdentityController
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
        $authenticationRequestId = $session->get('_signup_authentication_request_id');
        if (null === $authenticationRequestId) {
            return new RedirectResponse('/sign-up');
        }

        $result = $this->signUpAuthenticationRepository->findById($authenticationRequestId);
        if (null === $result) {
            return new RedirectResponse('/sign-up');
        }

        $authenticationRequest = SignUpAuthenticationModel::createFromArray(
            array_merge(['id' => $authenticationRequestId], $result)
        );

        $context = [
            'html_title' => 'Verify your email address',
            'name' => $authenticationRequest->getName(),
            'email_address' => $authenticationRequest->getEmailAddress(),
        ];

        return new Response(
            $this->twig->render('onboarding/verify_identity.twig', $context)
        );
    }
}
