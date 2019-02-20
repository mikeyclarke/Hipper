<?php
declare(strict_types=1);

namespace Lithos\Onboarding;

use Lithos\EmailAddressVerification\Exception\EmailAddressVerificationNotFoundException;
use Lithos\EmailAddressVerification\VerifyEmailAddress;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class VerifyIdentityController
{
    private $verifyEmailAddress;
    private $twig;

    public function __construct(
        VerifyEmailAddress $verifyEmailAddress,
        Twig_Environment $twig
    ) {
        $this->verifyEmailAddress = $verifyEmailAddress;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $person = $request->attributes->get('person');

        $context = [
            'person' => $person,
        ];

        return new Response(
            $this->twig->render('onboarding/verify_identity.twig', $context)
        );
    }

    public function postAction(Request $request): Response
    {
        $person = $request->attributes->get('person');

        try {
            $this->verifyEmailAddress->verify($person->getId(), $request->request->get('phrase', ''));
        } catch (EmailAddressVerificationNotFoundException $e) {
            return new JsonResponse(null, 400);
        }

        return new JsonResponse(null, 200);
    }
}
