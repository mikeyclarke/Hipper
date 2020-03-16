<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use PDO;

class KnowledgebaseRepository
{
    private const DEFAULT_FIELDS = [
        'id',
        'entity',
        'organization_id',
    ];

    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function findById(string $knowledgebaseId, string $organizationId): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select(self::DEFAULT_FIELDS)
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

    public function getKnowledgebaseOwnersForIds(array $knowledgebaseIds, string $organizationId): array
    {
        $sql = <<<SQL
SELECT *
FROM (
    SELECT
		name,
		url_id,
		knowledgebase_id,
		'team' AS entity
    FROM team
    WHERE knowledgebase_id IN (?) AND organization_id = ?

    UNION ALL

    SELECT
		name,
		url_id,
		knowledgebase_id,
		'project' AS entity
    FROM project
    WHERE knowledgebase_id IN (?) AND organization_id = ?
) AS foo;
SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            [
                $knowledgebaseIds,
                $organizationId,
                $knowledgebaseIds,
                $organizationId,
            ],
            [
                Connection::PARAM_STR_ARRAY,
                ParameterType::STRING,
                Connection::PARAM_STR_ARRAY,
                ParameterType::STRING,
            ]
        );
        return $stmt->fetchAll();
    }
}
