<?php
namespace hleo\Controller;

use Base64Url\Base64Url;
use hleo\EmailAddressVerification\Exception\EmailAddressVerificationNotFoundException;
use hleo\EmailAddressVerification\VerifyEmailAddress;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class VerifyEmailController
{
    private $verifyEmailAddress;

    public function __construct(
        VerifyEmailAddress $verifyEmailAddress
    ) {
        $this->verifyEmailAddress = $verifyEmailAddress;
    }

    public function getAction(Request $request)
    {
        $personId = Base64Url::decode($request->query->get('p'));
        $verificationId = Base64Url::decode($request->query->get('id'));
        $verificationHash = Base64Url::decode($request->query->get('h'));

        try {
            $this->verifyEmailAddress->verify($personId, $verificationId, $verificationHash);
            return new RedirectResponse('/welcome/team');
        } catch (EmailAddressVerificationNotFoundException $e) {
            // HANDLE ME
        }
    }
}
