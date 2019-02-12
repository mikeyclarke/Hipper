<?php
declare(strict_types=1);

namespace Lithos\Team;

use Doctrine\DBAL\Connection;

class TeamInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(
        string $id,
        string $name,
        string $urlId,
        string $knowledgebaseId,
        string $organizationId
    ): array {
        $stmt = $this->connection->executeQuery(
            "INSERT INTO team (id, name, url_id, knowledgebase_id, organization_id) " .
            "VALUES (?, ?, ?, ?, ?) RETURNING *",
            [$id, $name, $urlId, $knowledgebaseId, $organizationId]
        );
        $result = $stmt->fetch();
        return $result;
    }
}