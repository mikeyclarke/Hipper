<?php
declare(strict_types=1);

namespace hleo\EmailAddressVerification;

use hleo\IdGenerator\IdGenerator;
use hleo\Person\PersonModel;
use hleo\TransactionalEmail\VerifyEmailAddressEmail;

class RequestEmailAddressVerification
{
    private $emailAddressVerificationInserter;
    private $idGenerator;
    private $verificationPhraseGenerator;
    private $verifyEmailAddressEmail;

    public function __construct(
        EmailAddressVerificationInserter $emailAddressVerificationInserter,
        IdGenerator $idGenerator,
        VerificationPhraseGenerator $verificationPhraseGenerator,
        VerifyEmailAddressEmail $verifyEmailAddressEmail
    ) {
        $this->emailAddressVerificationInserter = $emailAddressVerificationInserter;
        $this->idGenerator = $idGenerator;
        $this->verificationPhraseGenerator = $verificationPhraseGenerator;
        $this->verifyEmailAddressEmail = $verifyEmailAddressEmail;
    }

    public function sendVerificationRequest(PersonModel $person): void
    {
        $verificationId = $this->idGenerator->generate();
        $verificationPhrase = $this->verificationPhraseGenerator->generate();
        $expiryDate = new \DateTime('+ 5 hours');

        $this->emailAddressVerificationInserter->insert(
            $verificationId,
            $person->getId(),
            $verificationPhrase,
            $expiryDate->format('Y-m-d H:i:s')
        );

        $this->verifyEmailAddressEmail->send(
            $person->getName(),
            $person->getEmailAddress(),
            $verificationPhrase
        );
    }
}
