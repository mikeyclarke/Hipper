<?php
declare(strict_types=1);

namespace Lithos\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Lithos\EmailAddressVerification\RequestEmailAddressVerification;
use Lithos\Organization\Organization;
use Lithos\Person\PersonCreator;
use Lithos\Person\PersonValidator;

class CreateFoundingMember
{
    private $connection;
    private $organization;
    private $personCreator;
    private $personValidator;
    private $requestEmailAddressVerification;

    public function __construct(
        Connection $connection,
        Organization $organization,
        PersonCreator $personCreator,
        PersonValidator $personValidator,
        RequestEmailAddressVerification $requestEmailAddressVerification
    ) {
        $this->connection = $connection;
        $this->organization = $organization;
        $this->personCreator = $personCreator;
        $this->personValidator = $personValidator;
        $this->requestEmailAddressVerification = $requestEmailAddressVerification;
    }

    public function create(array $input): array
    {
        $this->personValidator->validate($input, true);

        $this->connection->beginTransaction();
        try {
            $organization = $this->organization->create();
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
}
