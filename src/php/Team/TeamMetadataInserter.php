<?php
declare(strict_types=1);

namespace Hipper\Team;

use Doctrine\DBAL\Connection;

class TeamMetadataInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(string $id, string $teamId): void
    {
        $this->connection->insert(
            'team_metadata',
            [
                'id' => $id,
                'team_id' => $teamId,
            ]
        );
    }
}
