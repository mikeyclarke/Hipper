<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use PDO;

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

    public function getKnowledgebaseOwnersForIds(array $knowledgebaseIds, string $organizationId): array
    {
        $sql = <<<SQL
SELECT
    t.name AS "t.name",
    t.url_id AS "t.url_id",
    p.name AS "p.name",
    p.url_id AS "p.url_id",
    kb.id AS knowledgebase_id,
    kb.entity
FROM knowledgebase kb
    LEFT OUTER JOIN team t ON t.knowledgebase_id IN (?) AND t.organization_id = ? AND kb.entity = 'team'
    LEFT OUTER JOIN project p ON p.knowledgebase_id IN (?) AND p.organization_id = ? AND kb.entity = 'project'
WHERE kb.id IN (?) AND kb.organization_id = ?
SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            [
                $knowledgebaseIds,
                $organizationId,
                $knowledgebaseIds,
                $organizationId,
                $knowledgebaseIds,
                $organizationId
            ],
            [
                Connection::PARAM_STR_ARRAY,
                ParameterType::STRING,
                Connection::PARAM_STR_ARRAY,
                ParameterType::STRING,
                Connection::PARAM_STR_ARRAY,
                ParameterType::STRING
            ]
        );
        $result = $stmt->fetchAll();

        $final = [];
        foreach ($result as $row) {
            if ($row['entity'] === 'team') {
                $final[] = [
                    'entity' => $row['entity'],
                    'knowledgebase_id' => $row['knowledgebase_id'],
                    'name' => $row['t.name'],
                    'url_id' => $row['t.url_id'],
                ];
            }

            if ($row['entity'] === 'project') {
                $final[] = [
                    'entity' => $row['entity'],
                    'knowledgebase_id' => $row['knowledgebase_id'],
                    'name' => $row['p.name'],
                    'url_id' => $row['p.url_id'],
                ];
            }
        }

        return $final;
    }
}
