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

    public function findById(string $knowledgebaseId, string $organizationId): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from('knowledgebase')
            ->andWhere('id = :id')
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
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
