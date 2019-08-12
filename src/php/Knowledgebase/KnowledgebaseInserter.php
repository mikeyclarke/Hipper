<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

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
        $sql = "INSERT INTO knowledgebase (id, organization_id) VALUES (:id, :organization_id) RETURNING *";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->execute();
        return $stmt->fetch();
    }
}
