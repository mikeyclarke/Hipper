<?php
declare(strict_types=1);

namespace Hipper\Invite;

use Doctrine\DBAL\Connection;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Invite\Storage\InviteInserter;
use Hipper\Messenger\MessageBus;
use Hipper\Messenger\Message\InvitationsCreated;
use Hipper\Person\PersonModel;

class BulkInvitationCreator
{
    private BulkInvitationValidator $validator;
    private Connection $connection;
    private IdGenerator $idGenerator;
    private InviteInserter $inviteInserter;
    private MessageBus $messageBus;

    public function __construct(
        BulkInvitationValidator $validator,
        Connection $connection,
        IdGenerator $idGenerator,
        InviteInserter $inviteInserter,
        MessageBus $messageBus
    ) {
        $this->validator = $validator;
        $this->connection = $connection;
        $this->idGenerator = $idGenerator;
        $this->inviteInserter = $inviteInserter;
        $this->messageBus = $messageBus;
    }

    public function create(PersonModel $person, string $domain, array $input): void
    {
        $this->validator->validate($input);
        if (!isset($input['email_invites']) || empty($input['email_invites'])) {
            return;
        }

        $organizationId = $person->getOrganizationId();
        $personId = $person->getId();

        $this->connection->beginTransaction();
        try {
            $inviteIds = $this->insertInvites($organizationId, $personId, $input);
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $this->messageBus->dispatch(new InvitationsCreated($organizationId, $personId, $domain, $inviteIds));
    }

    private function insertInvites(string $organizationId, string $personId, array $input): array
    {
        $expiryDate = new \DateTime('+ 30 days');
        $expires = $expiryDate->format('Y-m-d H:i:s');
        $inviteIds = [];

        foreach ($input['email_invites'] as $emailAddress) {
            $id = $this->idGenerator->generate();
            $inviteIds[] = $id;

            $this->inviteInserter->insert(
                $id,
                $emailAddress,
                $personId,
                $organizationId,
                $expires
            );
        }

        return $inviteIds;
    }
}
