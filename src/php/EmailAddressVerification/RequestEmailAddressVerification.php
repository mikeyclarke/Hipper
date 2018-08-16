<?php
namespace hleo\EmailAddressVerification;

use Base64Url\Base64Url;
use hleo\IdGenerator\IdGenerator;
use hleo\Person\PersonModel;
use hleo\TransactionalEmail\VerifyEmailAddressEmail;
use RandomLib\Factory as RandomLibFactory;

class RequestEmailAddressVerification
{
    private $emailAddressVerificationInserter;
    private $idGenerator;
    private $verifyEmailAddressEmail;

    public function __construct(
        EmailAddressVerificationInserter $emailAddressVerificationInserter,
        IdGenerator $idGenerator,
        VerifyEmailAddressEmail $verifyEmailAddressEmail
    ) {
        $this->emailAddressVerificationInserter = $emailAddressVerificationInserter;
        $this->idGenerator = $idGenerator;
        $this->verifyEmailAddressEmail = $verifyEmailAddressEmail;
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
        return sprintf(
            'http://127.0.0.1:8000/m/verify-email?p=%s&id=%s&h=%s',
            Base64Url::encode($personId),
            Base64Url::encode($verificationId),
            Base64Url::encode($verificationHash)
        );
    }

    private function createVerificationHash()
    {
        $factory = new RandomLibFactory;
        $generator = $factory->getMediumStrengthGenerator();
        return $generator->generateString(32);
    }
}
