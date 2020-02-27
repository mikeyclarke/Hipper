<?php
declare(strict_types=1);

namespace Hipper\Team\Storage;

use Doctrine\DBAL\Connection;

class PersonToTeamMapInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(string $id, string $personId, string $teamId): void
    {
        $this->connection->insert(
            'person_to_team_map',
            [
                'id' => $id,
                'person_id' => $personId,
                'team_id' => $teamId,
            ]
        );
    }
}
