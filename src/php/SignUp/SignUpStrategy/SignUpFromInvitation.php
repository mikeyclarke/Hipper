<?php
declare(strict_types=1);

namespace Hipper\SignUp\SignUpStrategy;

use Doctrine\DBAL\Connection;
use Hipper\Invite\InviteModel;
use Hipper\Invite\InviteRepository;
use Hipper\Invite\Storage\InviteDeleter;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonRepository;
use Hipper\SignUp\Exception\EmailAddressAlreadyInUseException;
use Hipper\SignUp\Exception\InviteExpiredException;
use Hipper\SignUp\Exception\InviteNotFoundException;
use Hipper\SignUp\SignUpValidation\InvitationSignUpValidator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SignUpFromInvitation
{
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private InvitationSignUpValidator $validator;
    private InviteDeleter $inviteDeleter;
    private InviteRepository $inviteRepository;
    private PersonCreator $personCreator;
    private PersonRepository $personRepository;

    public function __construct(
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        InvitationSignUpValidator $validator,
        InviteDeleter $inviteDeleter,
        InviteRepository $inviteRepository,
        PersonCreator $personCreator,
        PersonRepository $personRepository
    ) {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->inviteDeleter = $inviteDeleter;
        $this->inviteRepository = $inviteRepository;
        $this->personCreator = $personCreator;
        $this->personRepository = $personRepository;
    }

    public function signUp(OrganizationModel $organization, array $input): PersonModel
    {
        $this->validator->validate($input);

        $result = $this->inviteRepository->find($input['invite_id'], $organization->getId(), $input['invite_token']);
        if (null === $result) {
            throw new InviteNotFoundException;
        }

        $invite = InviteModel::createFromArray($result);
        if ($invite->hasExpired()) {
            throw new InviteExpiredException;
        }

        if ($this->personRepository->existsWithEmailAddress($invite->getEmailAddress())) {
            throw new EmailAddressAlreadyInUseException;
        }

        $this->connection->beginTransaction();
        try {
            $person = $this->personCreator->create(
                $organization,
                $input['name'],
                $invite->getEmailAddress(),
                $input['password']
            );
            $this->inviteDeleter->delete($invite->getId());
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
