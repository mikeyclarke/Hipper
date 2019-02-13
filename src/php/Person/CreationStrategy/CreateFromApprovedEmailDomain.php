<?php
declare(strict_types=1);

namespace Lithos\Person\CreationStrategy;

use Lithos\EmailAddressVerification\RequestEmailAddressVerification;
use Lithos\Organization\OrganizationModel;
use Lithos\Person\Exception\ApprovedEmailDomainSignupNotAllowedException;
use Lithos\Person\Exception\MalformedEmailAddressException;
use Lithos\Person\PersonCreator;
use Lithos\Person\PersonValidator;
use Lithos\Validation\Exception\ValidationException;

class CreateFromApprovedEmailDomain
{
    private $personCreator;
    private $personValidator;
    private $requestEmailAddressVerification;

    public function __construct(
        PersonCreator $personCreator,
        PersonValidator $personValidator,
        RequestEmailAddressVerification $requestEmailAddressVerification
    ) {
        $this->personCreator = $personCreator;
        $this->personValidator = $personValidator;
        $this->requestEmailAddressVerification = $requestEmailAddressVerification;
    }

    public function create(OrganizationModel $organization, array $input): array
    {
        $this->personValidator->validate($input, true);

        if (!$organization->isApprovedEmailDomainSignupAllowed()) {
            throw new ApprovedEmailDomainSignupNotAllowedException;
        }

        if (!$this->isEmailDomainAllowed($organization, $input)) {
            throw new ValidationException([
                'email_address' => [
                    'Email address domain is not approved for sign-up.',
                ]
            ]);
        }

        list($person, $encodedPassword) = $this->personCreator->create(
            $organization,
            $input['name'],
            $input['email_address'],
            $input['password']
        );

        $this->requestEmailAddressVerification->sendVerificationRequest($person);

        return [$person, $encodedPassword];
    }

    private function isEmailDomainAllowed(OrganizationModel $organization, array $input): bool
    {
        $allowedDomains = $organization->getApprovedEmailDomains();
        if (null === $allowedDomains) {
            return false;
        }

        $parts = explode('@', $input['email_address']);
        if (count($parts) !== 2) {
            throw new MalformedEmailAddressException;
        }

        $allowedDomains = explode(',', $allowedDomains);
        $inputDomain = $parts[1];
        return in_array($inputDomain, $allowedDomains);
    }
}
