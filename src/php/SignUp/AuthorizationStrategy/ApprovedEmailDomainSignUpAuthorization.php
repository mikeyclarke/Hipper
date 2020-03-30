<?php
declare(strict_types=1);

namespace Hipper\SignUp\AuthorizationStrategy;

use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonPasswordEncoder;
use Hipper\SignUp\AuthorizationValidation\ApprovedEmailDomainSignUpAuthorizationValidator;
use Hipper\SignUp\SignUpAuthorization;
use Hipper\SignUp\SignUpAuthorizationRequestModel;

class ApprovedEmailDomainSignUpAuthorization
{
    private ApprovedEmailDomainSignUpAuthorizationValidator $validator;
    private PersonPasswordEncoder $passwordEncoder;
    private SignUpAuthorization $signUpAuthorization;

    public function __construct(
        ApprovedEmailDomainSignUpAuthorizationValidator $validator,
        PersonPasswordEncoder $passwordEncoder,
        SignUpAuthorization $signUpAuthorization
    ) {
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
        $this->signUpAuthorization = $signUpAuthorization;
    }

    public function request(OrganizationModel $organization, array $input): SignUpAuthorizationRequestModel
    {
        $this->validator->validate($input, $organization);

        $encodedPassword = $this->passwordEncoder->encodePassword($input['password']);

        $authenticationRequest = $this->signUpAuthorization->request(
            $input['email_address'],
            $input['name'],
            $encodedPassword,
            $organization->getId()
        );
        return $authenticationRequest;
    }
}
