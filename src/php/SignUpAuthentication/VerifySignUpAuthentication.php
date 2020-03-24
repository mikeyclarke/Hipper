<?php
declare(strict_types=1);

namespace Hipper\SignUpAuthentication;

use Hipper\Organization\OrganizationModel;
use Hipper\SignUpAuthentication\Exception\AuthenticationRequestForeignToOrganizationException;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationRepository;
use Hipper\Validation\Exception\ValidationException;

class VerifySignUpAuthentication
{
    private SignUpAuthenticationRepository $signUpAuthenticationRepository;

    public function __construct(
        SignUpAuthenticationRepository $signUpAuthenticationRepository
    ) {
        $this->signUpAuthenticationRepository = $signUpAuthenticationRepository;
    }

    public function verifyWithPhrase(
        string $authenticationRequestId,
        string $inputPhrase,
        ?OrganizationModel $organization = null
    ): SignUpAuthenticationModel {
        $result = $this->signUpAuthenticationRepository->findById($authenticationRequestId);
        if (null === $result) {
            $this->throwValidationException('Your email verification has expired, please sign-up again');
        }

        $authenticationRequest = SignUpAuthenticationModel::createFromArray(
            array_merge(['id' => $authenticationRequestId], $result)
        );

        if (null !== $organization && $authenticationRequest->getOrganizationId() !== $organization->getId()) {
            throw new AuthenticationRequestForeignToOrganizationException;
        }

        if ($inputPhrase !== $authenticationRequest->getVerificationPhrase()) {
            $this->throwValidationException('Incorrect verification phrase');
        }

        return $authenticationRequest;
    }

    private function throwValidationException(string $message): void
    {
        throw new ValidationException([
            'phrase' => [
                $message,
            ],
        ]);
    }
}
