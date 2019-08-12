<?php
declare(strict_types=1);

namespace Hipper\EmailAddressVerification;

use Hipper\EmailAddressVerification\Exception\EmailAddressVerificationNotFoundException;
use Hipper\Person\PersonUpdater;

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

    public function verify(string $personId, string $verificationPhrase): void
    {
        $result = $this->emailAddressVerificationRepository->get($personId, $verificationPhrase);
        if (null === $result) {
            throw new EmailAddressVerificationNotFoundException;
        }

        $this->personUpdater->update($personId, ['email_address_verified' => true]);
    }
}
