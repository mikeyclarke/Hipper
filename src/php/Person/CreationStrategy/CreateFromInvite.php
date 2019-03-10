<?php
declare(strict_types=1);

namespace Lithos\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Lithos\Invite\InviteDeleter;
use Lithos\Invite\InviteRepository;
use Lithos\Organization\OrganizationModel;
use Lithos\Person\Exception\InviteNotFoundException;
use Lithos\Person\PersonCreationValidator;
use Lithos\Person\PersonCreator;

class CreateFromInvite
{
    private $connection;
    private $inviteDeleter;
    private $inviteRepository;
    private $personCreationValidator;
    private $personCreator;

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
