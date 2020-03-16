<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase\Storage;

use Doctrine\DBAL\Connection;

class KnowledgebaseInserter
{
    private const FIELDS_TO_RETURN = [
        'id',
        'entity',
        'organization_id',
        'created',
    ];

    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(string $id, string $entity, string $organizationId): array
    {
        $fieldsToReturn = implode(', ', self::FIELDS_TO_RETURN);
        $sql = "INSERT INTO knowledgebase (id, entity, organization_id) " .
            "VALUES (:id, :entity, :organization_id) RETURNING {$fieldsToReturn}";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->bindValue('entity', $entity);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->execute();
        return $stmt->fetch();
    }
}
