<?php
declare(strict_types=1);

namespace Hipper\SignUpAuthentication;

use Hipper\EmailAddressVerification\VerificationPhraseGenerator;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Person\PersonCreationValidator;
use Hipper\Person\PersonPasswordEncoder;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\SignUpAuthentication\Storage\SignUpAuthenticationInserter;
use Hipper\TransactionalEmail\VerifyEmailAddressEmail;

class SignUpAuthenticationRequest
{
    private IdGenerator $idGenerator;
    private PersonCreationValidator $personCreationValidator;
    private PersonPasswordEncoder $passwordEncoder;
    private SignUpAuthenticationInserter $inserter;
    private VerificationPhraseGenerator $verificationPhraseGenerator;
    private VerifyEmailAddressEmail $verifyEmailAddressEmail;

    public function __construct(
        IdGenerator $idGenerator,
        PersonCreationValidator $personCreationValidator,
        PersonPasswordEncoder $passwordEncoder,
        SignUpAuthenticationInserter $inserter,
        VerificationPhraseGenerator $verificationPhraseGenerator,
        VerifyEmailAddressEmail $verifyEmailAddressEmail
    ) {
        $this->idGenerator = $idGenerator;
        $this->personCreationValidator = $personCreationValidator;
        $this->passwordEncoder = $passwordEncoder;
        $this->inserter = $inserter;
        $this->verificationPhraseGenerator = $verificationPhraseGenerator;
        $this->verifyEmailAddressEmail = $verifyEmailAddressEmail;
    }

    public function create(array $input): SignUpAuthenticationModel
    {
        $this->personCreationValidator->validate($input, 'sign_up_authentication');

        $id = $this->idGenerator->generate();
        $verificationPhrase = $this->verificationPhraseGenerator->generate();
        $encodedPassword = $this->passwordEncoder->encodePassword($input['password']);

        $this->inserter->insert(
            $id,
            $verificationPhrase,
            $input['email_address'],
            $input['name'],
            $encodedPassword
        );

        $this->verifyEmailAddressEmail->send(
            $input['name'],
            $input['email_address'],
            $verificationPhrase
        );

        $authenticationRequest = SignUpAuthenticationModel::createFromArray([
            'id' => $id,
            'name' => $input['name'],
            'email_address' => $input['email_address'],
            'verification_phrase' => $verificationPhrase,
            'encoded_password' => $encodedPassword,
        ]);
        return $authenticationRequest;
    }
}
