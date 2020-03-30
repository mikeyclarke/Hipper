<?php
declare(strict_types=1);

namespace Hipper\SignUp;

use Hipper\EmailAddressVerification\VerificationPhraseGenerator;
use Hipper\IdGenerator\IdGenerator;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Hipper\SignUp\Storage\SignUpAuthorizationRequestInserter;
use Hipper\TransactionalEmail\VerifyEmailAddressEmail;
use Hipper\Validation\ConstraintViolationListFormatter;
use Hipper\Validation\Constraints\SignUpAuthorizationPhrase;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SignUpAuthorization
{
    private IdGenerator $idGenerator;
    private SignUpAuthorizationRequestInserter $inserter;
    private ValidatorInterface $validatorInterface;
    private VerificationPhraseGenerator $verificationPhraseGenerator;
    private VerifyEmailAddressEmail $verifyEmailAddressEmail;

    public function __construct(
        IdGenerator $idGenerator,
        SignUpAuthorizationRequestInserter $inserter,
        ValidatorInterface $validatorInterface,
        VerificationPhraseGenerator $verificationPhraseGenerator,
        VerifyEmailAddressEmail $verifyEmailAddressEmail
    ) {
        $this->idGenerator = $idGenerator;
        $this->inserter = $inserter;
        $this->validatorInterface = $validatorInterface;
        $this->verificationPhraseGenerator = $verificationPhraseGenerator;
        $this->verifyEmailAddressEmail = $verifyEmailAddressEmail;
    }

    public function request(
        string $emailAddress,
        string $name,
        string $encodedPassword,
        ?string $organizationId = null,
        ?string $organizationName = null
    ): SignUpAuthorizationRequestModel {
        $id = $this->idGenerator->generate();
        $verificationPhrase = $this->verificationPhraseGenerator->generate();

        $this->inserter->insert(
            $id,
            $verificationPhrase,
            $emailAddress,
            $name,
            $encodedPassword,
            $organizationId,
            $organizationName
        );

        $this->verifyEmailAddressEmail->send(
            $name,
            $emailAddress,
            $verificationPhrase
        );

        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray([
            'id' => $id,
            'name' => $name,
            'email_address' => $emailAddress,
            'verification_phrase' => $verificationPhrase,
            'encoded_password' => $encodedPassword,
            'organization_id' => $organizationId,
            'organization_name' => $organizationName,
        ]);
        return $authorizationRequest;
    }

    public function authorize(SignUpAuthorizationRequestModel $authorizationRequest, array $input): void
    {
        $collectionConstraint = new Collection([
            'phrase' => new Required([
                new SignUpAuthorizationPhrase([
                    'authorizationRequest' => $authorizationRequest,
                ]),
            ]),
        ]);

        $violations = $this->validatorInterface->validate($input, $collectionConstraint);

        if (count($violations) > 0) {
            throw new ValidationException(
                ConstraintViolationListFormatter::format($violations)
            );
        }
    }
}
