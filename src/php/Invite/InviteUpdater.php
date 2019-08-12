<?php
declare(strict_types=1);

namespace Hipper\Invite;

use Doctrine\DBAL\Connection;

class InviteUpdater
{
    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function update(string $id, array $properties): void
    {
        $this->connection->update(
            'invite',
            $properties,
            ['id' => $id]
        );
    }
}
