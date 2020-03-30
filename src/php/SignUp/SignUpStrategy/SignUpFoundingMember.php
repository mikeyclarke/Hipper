<?php
declare(strict_types=1);

namespace Hipper\SignUp\SignUpStrategy;

use Doctrine\DBAL\Connection;
use Hipper\Organization\Event\OrganizationCreatedEvent;
use Hipper\Organization\OrganizationCreator;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonRepository;
use Hipper\SignUp\Exception\AuthorizationRequestMissingOrganizationNameException;
use Hipper\SignUp\Exception\EmailAddressAlreadyInUseException;
use Hipper\SignUp\SignUpAuthorization;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SignUpFoundingMember
{
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private OrganizationCreator $organizationCreator;
    private PersonCreator $personCreator;
    private PersonRepository $personRepository;
    private SignUpAuthorization $signUpAuthorization;

    public function __construct(
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        OrganizationCreator $organizationCreator,
        PersonCreator $personCreator,
        PersonRepository $personRepository,
        SignUpAuthorization $signUpAuthorization
    ) {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->organizationCreator = $organizationCreator;
        $this->personCreator = $personCreator;
        $this->personRepository = $personRepository;
        $this->signUpAuthorization = $signUpAuthorization;
    }

    public function signUp(SignUpAuthorizationRequestModel $authorizationRequest, array $input): PersonModel
    {
        $this->signUpAuthorization->authorize($authorizationRequest, $input);

        $organizationName = $authorizationRequest->getOrganizationName();
        if (null === $organizationName) {
            throw new AuthorizationRequestMissingOrganizationNameException;
        }

        if ($this->personRepository->existsWithEmailAddress($authorizationRequest->getEmailAddress())) {
            throw new EmailAddressAlreadyInUseException;
        }

        $this->connection->beginTransaction();
        try {
            $organization = $this->organizationCreator->create($organizationName);
            $person = $this->personCreator->createWithEncodedPassword(
                $organization,
                $authorizationRequest->getName(),
                $authorizationRequest->getEmailAddress(),
                $authorizationRequest->getEncodedPassword(),
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
