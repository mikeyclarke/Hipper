<?php
declare(strict_types=1);

namespace Hipper\Invite\Storage;

use Doctrine\DBAL\Connection;

class InviteUpdater
{
    private $connection;

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
