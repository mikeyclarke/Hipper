<?php
declare(strict_types=1);

namespace Hipper\SignUp\AuthorizationStrategy;

use Hipper\Person\PersonPasswordEncoder;
use Hipper\SignUp\AuthorizationValidation\FoundingMemberSignUpAuthorizationValidator;
use Hipper\SignUp\SignUpAuthorization;
use Hipper\SignUp\SignUpAuthorizationRequestModel;

class FoundingMemberSignUpAuthorization
{
    private FoundingMemberSignUpAuthorizationValidator $validator;
    private PersonPasswordEncoder $passwordEncoder;
    private SignUpAuthorization $signUpAuthorization;

    public function __construct(
        FoundingMemberSignUpAuthorizationValidator $validator,
        PersonPasswordEncoder $passwordEncoder,
        SignUpAuthorization $signUpAuthorization
    ) {
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
        $this->signUpAuthorization = $signUpAuthorization;
    }

    public function request(array $input): SignUpAuthorizationRequestModel
    {
        $this->validator->validate($input);

        $encodedPassword = $this->passwordEncoder->encodePassword($input['password']);

        $authenticationRequest = $this->signUpAuthorization->request(
            $input['email_address'],
            $input['name'],
            $encodedPassword,
            null,
            $input['organization_name']
        );
        return $authenticationRequest;
    }
}
