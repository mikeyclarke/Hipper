<?php
namespace hleo\EmailAddressVerification;

use hleo\EmailAddressVerification\Exception\EmailAddressVerificationNotFoundException;
use hleo\Person\PersonUpdater;

class VerifyEmailAddress
{
    private $emailAddressVerificationRepository;
    private $personUpdater;

    public function __construct(
        EmailAddressVerificationRepository $emailAddressVerificationRepository,
        PersonUpdater $personUpdater
    ) {
        $this->emailAddressVerificationRepository = $emailAddressVerificationRepository;
        $this->personUpdater = $personUpdater;
    }

    public function verify(string $personId, string $verificationId, string $verificationHash)
    {
        $result = $this->emailAddressVerificationRepository->get($personId, $verificationId, $verificationHash);
        if (null === $result) {
            throw new EmailAddressVerificationNotFoundException;
        }

        $this->personUpdater->update($personId, ['email_address_verified' => true]);
    }
}
