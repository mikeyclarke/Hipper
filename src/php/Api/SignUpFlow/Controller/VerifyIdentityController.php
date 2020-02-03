<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\EmailAddressVerification\Exception\EmailAddressVerificationNotFoundException;
use Hipper\EmailAddressVerification\VerifyEmailAddress;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class VerifyIdentityController
{
    private $verifyEmailAddress;

    public function __construct(
        VerifyEmailAddress $verifyEmailAddress
    ) {
        $this->verifyEmailAddress = $verifyEmailAddress;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');

        try {
            $this->verifyEmailAddress->verify($currentUser->getId(), $request->request->get('phrase', ''));
        } catch (EmailAddressVerificationNotFoundException $e) {
            return new JsonResponse(null, 400);
        }

        return new JsonResponse(null, 200);
    }
}
