<?php
declare(strict_types=1);

namespace Hipper\Invite\Storage;

use Doctrine\DBAL\Connection;

class InviteDeleter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function delete(string $id): void
    {
        $this->connection->delete(
            'invite',
            ['id' => $id]
        );
    }
}
