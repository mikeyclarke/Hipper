<?php
declare(strict_types=1);

namespace Hipper\Invite;

use Doctrine\DBAL\Connection;

class InviteInserter
{
    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(
        string $id,
        string $emailAddress,
        string $invitedBy,
        string $organizationId,
        string $expires
    ): void {
        $this->connection->insert(
            'invite',
            [
                'id' => $id,
                'email_address' => $emailAddress,
                'invited_by' => $invitedBy,
                'organization_id' => $organizationId,
                'expires' => $expires,
            ]
        );
    }
}
