<?php
declare(strict_types=1);

namespace hleo\Onboarding;

use hleo\EmailAddressVerification\Exception\EmailAddressVerificationNotFoundException;
use hleo\EmailAddressVerification\VerifyEmailAddress;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIdentityController
{
    private $verifyEmailAddress;

    public function __construct(
        VerifyEmailAddress $verifyEmailAddress
    ) {
        $this->verifyEmailAddress = $verifyEmailAddress;
    }

    public function getAction(Request $request): Response
    {
        return new Response(null);
    }

    public function postAction(Request $request): JsonResponse
    {
        if (!$request->hasPreviousSession()) {
            return new JsonResponse(null, 401);
        }

        $session = $request->getSession();
        $content = json_decode($request->getContent(), true);

        try {
            $this->verifyEmailAddress->verify($session->get('onboarding/personId'), $content['phrase']);
        } catch (EmailAddressVerificationNotFoundException $e) {
            return new JsonResponse(null, 400);
        }

        return new JsonResponse(null, 200);
    }
}
