<?php
declare(strict_types=1);

namespace Lithos\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Lithos\EmailAddressVerification\RequestEmailAddressVerification;
use Lithos\Organization\OrganizationModel;
use Lithos\Person\Exception\ApprovedEmailDomainSignupNotAllowedException;
use Lithos\Person\Exception\MalformedEmailAddressException;
use Lithos\Person\PersonCreationValidator;
use Lithos\Person\PersonCreator;
use Lithos\Validation\Exception\ValidationException;

class CreateFromApprovedEmailDomain
{
    private $connection;
    private $personCreationValidator;
    private $personCreator;
    private $requestEmailAddressVerification;

    public function __construct(
        Connection $connection,
        PersonCreationValidator $personCreationValidator,
        PersonCreator $personCreator,
        RequestEmailAddressVerification $requestEmailAddressVerification
    ) {
        $this->connection = $connection;
        $this->personCreationValidator = $personCreationValidator;
        $this->personCreator = $personCreator;
        $this->requestEmailAddressVerification = $requestEmailAddressVerification;
    }

    public function create(OrganizationModel $organization, array $input): array
    {
        $this->personCreationValidator->validate($input, 'approved_email_domain');

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

        $this->connection->beginTransaction();
        try {
            list($person, $encodedPassword) = $this->personCreator->create(
                $organization,
                $input['name'],
                $input['email_address'],
                $input['password']
            );
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

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

        $inputDomain = $parts[1];
        return in_array($inputDomain, $allowedDomains);
    }
}
