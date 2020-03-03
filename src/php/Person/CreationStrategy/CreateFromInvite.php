<?php
declare(strict_types=1);

namespace Hipper\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Hipper\Invite\InviteRepository;
use Hipper\Invite\Storage\InviteDeleter;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\Exception\InviteNotFoundException;
use Hipper\Person\PersonCreationValidator;
use Hipper\Person\PersonCreator;

class CreateFromInvite
{
    private Connection $connection;
    private InviteDeleter $inviteDeleter;
    private InviteRepository $inviteRepository;
    private PersonCreationValidator $personCreationValidator;
    private PersonCreator $personCreator;

    public function __construct(
        Connection $connection,
        InviteDeleter $inviteDeleter,
        InviteRepository $inviteRepository,
        PersonCreationValidator $personCreationValidator,
        PersonCreator $personCreator
    ) {
        $this->connection = $connection;
        $this->inviteDeleter = $inviteDeleter;
        $this->inviteRepository = $inviteRepository;
        $this->personCreationValidator = $personCreationValidator;
        $this->personCreator = $personCreator;
    }

    public function create(OrganizationModel $organization, array $input): array
    {
        $this->personCreationValidator->validate($input, 'invite');

        $invite = $this->inviteRepository->find($input['invite_id'], $organization->getId(), $input['invite_token']);
        if (null === $invite) {
            throw new InviteNotFoundException;
        }

        $this->connection->beginTransaction();
        try {
            list($person, $encodedPassword) = $this->personCreator->create(
                $organization,
                $input['name'],
                $invite['email_address'],
                $input['password'],
                true
            );
            $this->inviteDeleter->delete($invite['id']);
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        return [$person, $encodedPassword];
    }
}
