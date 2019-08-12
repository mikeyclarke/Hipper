<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Doctrine\DBAL\Connection;

class KnowledgebaseRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function exists(string $organizationId, string $knowledgebaseId): bool
    {
        $stmt = $this->connection->executeQuery(
            'SELECT EXISTS (
                SELECT 1 FROM knowledgebase WHERE organization_id = ? AND id = ?
            )',
            [$organizationId, $knowledgebaseId]
        );
        return (bool) $stmt->fetchColumn();
    }
}
