<?php
declare(strict_types=1);

namespace Hipper\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\PersonCreationValidator;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateFromApprovedEmailDomain
{
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private PersonCreationValidator $personCreationValidator;
    private PersonCreator $personCreator;

    public function __construct(
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        PersonCreationValidator $personCreationValidator,
        PersonCreator $personCreator
    ) {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->personCreationValidator = $personCreationValidator;
        $this->personCreator = $personCreator;
    }

    public function create(
        OrganizationModel $organization,
        SignUpAuthenticationModel $authenticationRequest
    ): PersonModel {
        $parameters = [
            'email_address' => $authenticationRequest->getEmailAddress(),
            'name' => $authenticationRequest->getName(),
        ];
        $this->personCreationValidator->validate($parameters, $organization, ['approved_email_domain']);

        $this->connection->beginTransaction();
        try {
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

        $personCreatedEvent = new PersonCreatedEvent($person);
        $this->eventDispatcher->dispatch($personCreatedEvent, PersonCreatedEvent::NAME);

        return $person;
    }
}
