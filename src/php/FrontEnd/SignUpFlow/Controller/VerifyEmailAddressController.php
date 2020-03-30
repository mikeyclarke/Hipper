<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\SignUpFlow\Controller;

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
        $authorizationRequestId = $session->get('_signup_authorization_request_id');
        if (null === $authorizationRequestId) {
            return new RedirectResponse('/sign-up');
        }

        $result = $this->signUpAuthorizationRequestRepository->findById($authorizationRequestId);
        if (null === $result) {
            return new RedirectResponse('/sign-up');
        }

        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray(
            array_merge(['id' => $authorizationRequestId], $result)
        );

        $context = [
            'html_title' => 'Verify your email address',
            'email_address' => $authorizationRequest->getEmailAddress(),
        ];

        return new Response(
            $this->twig->render('sign_up_flow/verify_email_address.twig', $context)
        );
    }
}
