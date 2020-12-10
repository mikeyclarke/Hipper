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
		url_slug,
		knowledgebase_id,
		'team' AS entity
    FROM team
    WHERE knowledgebase_id IN (?) AND organization_id = ?

    UNION ALL

    SELECT
		name,
		url_slug,
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

    public function getContents(string $knowledgebaseId, string $organizationId): array
    {
        $sql = <<<SQL
WITH RECURSIVE tree AS (
    SELECT
        id,
        name,
        url_id,
        url_slug,
        parent_topic_id,
        knowledgebase_id,
        organization_id,
        'topic' AS type,
        NULL AS content
    FROM topic
    WHERE knowledgebase_id = :knowledgebase_id AND organization_id = :organization_id AND parent_topic_id IS NULL

    UNION ALL

    SELECT
        id,
        name,
        url_id,
        url_slug,
        topic_id AS parent_topic_id,
        knowledgebase_id,
        organization_id,
        'document' AS type,
        content
    FROM document
    WHERE knowledgebase_id = :knowledgebase_id AND organization_id = :organization_id AND topic_id IS NULL

    UNION ALL

    (
        SELECT
            child.id,
            child.name,
            child.url_id,
            child.url_slug,
            child.parent_topic_id,
            child.knowledgebase_id,
            child.organization_id,
            child.type,
            child.content
        FROM
        (
            SELECT
                id,
                name,
                url_id,
                url_slug,
                topic_id AS parent_topic_id,
                knowledgebase_id,
                organization_id,
                'document' AS type,
                content
            FROM document
            WHERE knowledgebase_id = :knowledgebase_id AND organization_id = :organization_id

            UNION ALL

            SELECT
                id,
                name,
                url_id,
                url_slug,
                parent_topic_id,
                knowledgebase_id,
                organization_id,
                'topic' AS type,
                NULL AS content
            FROM topic
            WHERE knowledgebase_id = :knowledgebase_id AND organization_id = :organization_id
        ) child, tree tree
        WHERE child.parent_topic_id = tree.id
    )
)

SELECT * FROM tree;
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('knowledgebase_id', $knowledgebaseId, PDO::PARAM_STR);
        $stmt->bindValue('organization_id', $organizationId, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
