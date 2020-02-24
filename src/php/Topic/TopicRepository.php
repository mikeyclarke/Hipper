<?php
declare(strict_types=1);

namespace Hipper\Topic;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use PDO;

class TopicRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function findById(string $id, string $organizationId): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('topic')
            ->andWhere('id = :id')
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'id' => $id,
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function findByIdInKnowledgebase(string $id, string $knowledgebaseId, string $organizationId): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('topic')
            ->andWhere('id = :id')
            ->andWhere('knowledgebase_id = :knowledgebase_id')
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'id' => $id,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function getAllForKnowledgebaseInTopic(
        string $knowledgebaseId,
        ?string $topicId,
        string $organizationId
    ): array {
        $fields = [
            'topic.id',
            'topic.name',
            'topic.description',
            'topic.updated',
            'route.route',
            'route.url_id',
        ];

        $qb = $this->connection->createQueryBuilder();
        $qb->select($fields)
            ->from('topic', 'topic')
            ->innerJoin(
                'topic',
                'knowledgebase_route',
                'route',
                'route.topic_id = topic.id AND route.is_canonical IS TRUE'
            )
            ->andWhere('topic.knowledgebase_id = :knowledgebase_id')
            ->andWhere('topic.organization_id = :organization_id');

        if (null === $topicId) {
            $qb->andWhere('topic.parent_topic_id IS NULL');
        } else {
            $qb->andWhere('topic.parent_topic_id = :topic_id');
        }

        $qb->orderBy('topic.created', 'DESC');

        $qb->setParameters([
            'knowledgebase_id' => $knowledgebaseId,
            'topic_id' => $topicId,
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getNameAndAncestorNamesWithIds(array $ids, string $organizationId): ?array
    {
        $sql = <<<SQL
WITH RECURSIVE family AS (
    SELECT
        s1.id,
        s1.name,
        s1.parent_topic_id,
        s1.knowledgebase_id
    FROM topic s1
    WHERE s1.id IN (?) AND s1.organization_id = ?
    UNION ALL
    SELECT
        s2.id,
        s2.name,
        s2.parent_topic_id,
        s2.knowledgebase_id
    FROM topic s2
    JOIN family
    ON s2.id = family.parent_topic_id AND s2.organization_id = ?
)
SELECT * FROM family
SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            [
                $ids,
                $organizationId,
                $organizationId,
            ],
            [
                Connection::PARAM_STR_ARRAY,
                ParameterType::STRING,
                ParameterType::STRING,
            ]
        );
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getByIdWithAncestors(string $id, string $knowledgebaseId, string $organizationId): ?array
    {
        $sql = <<<SQL
WITH RECURSIVE family AS (
    SELECT
        s1.id,
        s1.name,
        s1.parent_topic_id,
        kbr.url_id,
        kbr.route
    FROM topic s1
    INNER JOIN knowledgebase_route kbr
    ON kbr.topic_id = s1.id AND kbr.is_canonical IS TRUE
    WHERE s1.id = :id AND s1.knowledgebase_id = :knowledgebase_id AND s1.organization_id = :organization_id
    UNION ALL
    SELECT
        s2.id,
        s2.name,
        s2.parent_topic_id,
        kbr.url_id,
        kbr.route
    FROM topic s2
    JOIN family
    ON s2.id = family.parent_topic_id
    INNER JOIN knowledgebase_route kbr
    ON kbr.topic_id = s2.id AND kbr.is_canonical IS TRUE
)
SELECT * FROM family
SQL;

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue('id', $id, PDO::PARAM_STR);
        $stmt->bindValue('knowledgebase_id', $knowledgebaseId, PDO::PARAM_STR);
        $stmt->bindValue('organization_id', $organizationId, PDO::PARAM_STR);

        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getTopicAndDescendants(string $id, string $knowledgebaseId, string $organizationId): array
    {
        $sql = <<<SQL
WITH RECURSIVE tree AS (
    SELECT
        id,
        url_id,
        url_slug,
        parent_topic_id,
        knowledgebase_id,
        organization_id,
        'topic' AS type
    FROM topic
    WHERE id = :id AND knowledgebase_id = :knowledgebase_id AND organization_id = :organization_id

    UNION ALL

    (
        SELECT
            child.id,
            child.url_id,
            child.url_slug,
            child.parent_topic_id,
            child.knowledgebase_id,
            child.organization_id,
            child.type
        FROM
        (
            SELECT
                id,
                url_id,
                url_slug,
                topic_id AS parent_topic_id,
                knowledgebase_id,
                organization_id,
                'document' AS type
            FROM document
            WHERE knowledgebase_id = :knowledgebase_id AND organization_id = :organization_id

            UNION ALL

            SELECT
                id,
                url_id,
                url_slug,
                parent_topic_id,
                knowledgebase_id,
                organization_id,
                'topic' AS type
            FROM topic
            WHERE knowledgebase_id = :knowledgebase_id AND organization_id = :organization_id
        ) child, tree tree
        WHERE child.parent_topic_id = tree.id
    )
)

SELECT * FROM tree;
SQL;
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id, PDO::PARAM_STR);
        $stmt->bindValue('knowledgebase_id', $knowledgebaseId, PDO::PARAM_STR);
        $stmt->bindValue('organization_id', $organizationId, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
