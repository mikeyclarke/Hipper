<?php
declare(strict_types=1);

namespace Lithos\Knowledgebase;

use Doctrine\DBAL\Connection;

class KnowledgebaseInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(string $id, string $organizationId): array
    {
        $stmt = $this->connection->executeQuery(
            "INSERT INTO knowledgebase (id, organization_id) " .
            "VALUES (?, ?) RETURNING *",
            [$id, $organizationId]
        );
        $result = $stmt->fetch();
        return $result;
    }
}
