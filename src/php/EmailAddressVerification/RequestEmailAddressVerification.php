<?php
namespace hleo\EmailAddressVerification;

use Base64Url\Base64Url;
use hleo\AppConfiguration\AppUrlGenerator;
use hleo\IdGenerator\IdGenerator;
use hleo\Person\PersonModel;
use hleo\TransactionalEmail\VerifyEmailAddressEmail;
use RandomLib\Factory as RandomLibFactory;

class RequestEmailAddressVerification
{
    private $emailAddressVerificationInserter;
    private $idGenerator;
    private $verifyEmailAddressEmail;
    private $appUrlGenerator;

    public function __construct(
        EmailAddressVerificationInserter $emailAddressVerificationInserter,
        IdGenerator $idGenerator,
        VerifyEmailAddressEmail $verifyEmailAddressEmail,
        AppUrlGenerator $appUrlGenerator
    ) {
        $this->emailAddressVerificationInserter = $emailAddressVerificationInserter;
        $this->idGenerator = $idGenerator;
        $this->verifyEmailAddressEmail = $verifyEmailAddressEmail;
        $this->appUrlGenerator = $appUrlGenerator;
    }

    public function sendVerificationRequest(PersonModel $person)
    {
        $verificationId = $this->idGenerator->generate();
        $verificationHash = $this->createVerificationHash();
        $expiryDate = new \DateTime('+ 5 hours');

        $this->emailAddressVerificationInserter->insert(
            $verificationId,
            $person->getId(),
            $verificationHash,
            $expiryDate->format('Y-m-d H:i:s')
        );

        $verificationLink = $this->createVerificationLink($person->getId(), $verificationId, $verificationHash);

        $this->verifyEmailAddressEmail->send(
            $person->getName(),
            $person->getEmailAddress(),
            $verificationLink
        );
    }

    private function createVerificationLink($personId, $verificationId, $verificationHash)
    {
        return $this->appUrlGenerator->generate('/m/verify-email', [
            'p' => Base64Url::encode($personId),
            'id' => Base64Url::encode($verificationId),
            'h' => Base64Url::encode($verificationHash),
        ]);
    }

    private function createVerificationHash()
    {
        $factory = new RandomLibFactory;
        $generator = $factory->getMediumStrengthGenerator();
        return $generator->generateString(32);
    }
}
