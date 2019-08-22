<?php
declare(strict_types=1);

namespace Hipper\Project;

use Doctrine\DBAL\Connection;

class PersonToProjectMapInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(string $id, string $personId, string $projectId): void
    {
        $this->connection->insert(
            'person_to_project_map',
            [
                'id' => $id,
                'person_id' => $personId,
                'project_id' => $projectId,
            ]
        );
    }
}
