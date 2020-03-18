<?php
declare(strict_types=1);

namespace Hipper\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Hipper\Organization\Event\OrganizationCreatedEvent;
use Hipper\Organization\OrganizationCreator;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\PersonCreationValidator;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateFoundingMember
{
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private OrganizationCreator $organizationCreator;
    private PersonCreationValidator $personCreationValidator;
    private PersonCreator $personCreator;

    public function __construct(
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        OrganizationCreator $organizationCreator,
        PersonCreationValidator $personCreationValidator,
        PersonCreator $personCreator
    ) {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->organizationCreator = $organizationCreator;
        $this->personCreationValidator = $personCreationValidator;
        $this->personCreator = $personCreator;
    }

    public function create(SignUpAuthenticationModel $authenticationRequest): PersonModel
    {
        $this->personCreationValidator->validate([
            'email_address' => $authenticationRequest->getEmailAddress(),
            'name' => $authenticationRequest->getName(),
        ]);

        $this->connection->beginTransaction();
        try {
            $organization = $this->organizationCreator->create();
            $person = $this->personCreator->createWithEncodedPassword(
                $organization,
                $authenticationRequest->getName(),
                $authenticationRequest->getEmailAddress(),
                $authenticationRequest->getEncodedPassword(),
            );
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $organizationCreatedEvent = new OrganizationCreatedEvent($person);
        $this->eventDispatcher->dispatch($organizationCreatedEvent, OrganizationCreatedEvent::NAME);

        $personCreatedEvent = new PersonCreatedEvent($person);
        $this->eventDispatcher->dispatch($personCreatedEvent, PersonCreatedEvent::NAME);

        return $person;
    }
}
