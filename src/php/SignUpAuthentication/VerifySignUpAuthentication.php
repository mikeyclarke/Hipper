<?php
declare(strict_types=1);

namespace Hipper\SignUpAuthentication;

use Hipper\SignUpAuthentication\Exception\AuthenticationRequestNotFoundException;
use Hipper\SignUpAuthentication\Exception\IncorrectVerificationPhraseException;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationRepository;

class VerifySignUpAuthentication
{
    private SignUpAuthenticationRepository $signUpAuthenticationRepository;

    public function __construct(
        SignUpAuthenticationRepository $signUpAuthenticationRepository
    ) {
        $this->signUpAuthenticationRepository = $signUpAuthenticationRepository;
    }

    public function verifyWithPhrase(string $authenticationRequestId, string $inputPhrase): SignUpAuthenticationModel
    {
        $result = $this->signUpAuthenticationRepository->findById($authenticationRequestId);
        if (null === $result) {
            throw new AuthenticationRequestNotFoundException;
        }

        $authenticationRequest = SignUpAuthenticationModel::createFromArray(
            array_merge(['id' => $authenticationRequestId], $result)
        );

        if ($inputPhrase !== $authenticationRequest->getVerificationPhrase()) {
            throw new IncorrectVerificationPhraseException;
        }

        return $authenticationRequest;
    }
}
