<?php
declare(strict_types=1);

namespace Hipper\SignUp\SignUpStrategy;

use Doctrine\DBAL\Connection;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonRepository;
use Hipper\SignUp\Exception\AuthorizationRequestForeignToOrganizationException;
use Hipper\SignUp\Exception\AuthorizationRequestMissingOrganizationIdException;
use Hipper\SignUp\Exception\EmailAddressAlreadyInUseException;
use Hipper\SignUp\SignUpAuthorization;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SignUpFromApprovedEmailDomain
{
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private PersonCreator $personCreator;
    private PersonRepository $personRepository;
    private SignUpAuthorization $signUpAuthorization;

    public function __construct(
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        PersonCreator $personCreator,
        PersonRepository $personRepository,
        SignUpAuthorization $signUpAuthorization
    ) {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->personCreator = $personCreator;
        $this->personRepository = $personRepository;
        $this->signUpAuthorization = $signUpAuthorization;
    }

    public function signUp(
        SignUpAuthorizationRequestModel $authorizationRequest,
        OrganizationModel $organization,
        array $input
    ): PersonModel {
        $this->signUpAuthorization->authorize($authorizationRequest, $input);

        $organizationId = $authorizationRequest->getOrganizationId();
        if (null === $organizationId) {
            throw new AuthorizationRequestMissingOrganizationIdException;
        }

        if ($organizationId !== $organization->getId()) {
            throw new AuthorizationRequestForeignToOrganizationException;
        }

        if ($this->personRepository->existsWithEmailAddress($authorizationRequest->getEmailAddress())) {
            throw new EmailAddressAlreadyInUseException;
        }

        $this->connection->beginTransaction();
        try {
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

        $personCreatedEvent = new PersonCreatedEvent($person);
        $this->eventDispatcher->dispatch($personCreatedEvent, PersonCreatedEvent::NAME);

        return $person;
    }
}
