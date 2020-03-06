<?php
declare(strict_types=1);

namespace Hipper\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Hipper\EmailAddressVerification\RequestEmailAddressVerification;
use Hipper\Organization\Event\OrganizationCreatedEvent;
use Hipper\Organization\OrganizationCreator;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\PersonCreationValidator;
use Hipper\Person\PersonCreator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateFoundingMember
{
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private OrganizationCreator $organizationCreator;
    private PersonCreationValidator $personCreationValidator;
    private PersonCreator $personCreator;
    private RequestEmailAddressVerification $requestEmailAddressVerification;

    public function __construct(
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        OrganizationCreator $organizationCreator,
        PersonCreationValidator $personCreationValidator,
        PersonCreator $personCreator,
        RequestEmailAddressVerification $requestEmailAddressVerification
    ) {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->organizationCreator = $organizationCreator;
        $this->personCreationValidator = $personCreationValidator;
        $this->personCreator = $personCreator;
        $this->requestEmailAddressVerification = $requestEmailAddressVerification;
    }

    public function create(array $input): array
    {
        $this->personCreationValidator->validate($input, 'founding_member');

        $this->connection->beginTransaction();
        try {
            $organization = $this->organizationCreator->create();
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

        $organizationCreatedEvent = new OrganizationCreatedEvent($person);
        $personCreatedEvent = new PersonCreatedEvent($person);

        $this->eventDispatcher->dispatch($organizationCreatedEvent, OrganizationCreatedEvent::NAME);
        $this->eventDispatcher->dispatch($personCreatedEvent, PersonCreatedEvent::NAME);

        return [$person, $encodedPassword];
    }
}
