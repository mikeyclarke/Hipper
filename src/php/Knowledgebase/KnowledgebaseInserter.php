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

    public function insert(string $id, string $entity, string $organizationId): array
    {
        $sql = "INSERT INTO knowledgebase (id, entity, organization_id) " .
            "VALUES (:id, :entity, :organization_id) RETURNING *";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->bindValue('entity', $entity);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->execute();
        return $stmt->fetch();
    }
}
