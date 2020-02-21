<?php
declare(strict_types=1);

namespace Hipper\Section;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use PDO;

class SectionRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function findById(string $sectionId, string $organizationId): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('section')
            ->andWhere('id = :id')
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'id' => $sectionId,
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function findByIdInKnowledgebase(string $sectionId, string $knowledgebaseId, string $organizationId): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('section')
            ->andWhere('id = :id')
            ->andWhere('knowledgebase_id = :knowledgebase_id')
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'id' => $sectionId,
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

    public function getAllForKnowledgebaseInSection(
        string $knowledgebaseId,
        ?string $sectionId,
        string $organizationId
    ): array {
        $fields = [
            'section.id',
            'section.name',
            'section.description',
            'section.updated',
            'route.route',
            'route.url_id',
        ];

        $qb = $this->connection->createQueryBuilder();
        $qb->select($fields)
            ->from('section', 'section')
            ->innerJoin(
                'section',
                'knowledgebase_route',
                'route',
                'route.section_id = section.id AND route.is_canonical IS TRUE'
            )
            ->andWhere('section.knowledgebase_id = :knowledgebase_id')
            ->andWhere('section.organization_id = :organization_id');

        if (null === $sectionId) {
            $qb->andWhere('section.parent_section_id IS NULL');
        } else {
            $qb->andWhere('section.parent_section_id = :section_id');
        }

        $qb->orderBy('section.created', 'DESC');

        $qb->setParameters([
            'knowledgebase_id' => $knowledgebaseId,
            'section_id' => $sectionId,
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
        s1.parent_section_id,
        s1.knowledgebase_id
    FROM section s1
    WHERE s1.id IN (?) AND s1.organization_id = ?
    UNION ALL
    SELECT
        s2.id,
        s2.name,
        s2.parent_section_id,
        s2.knowledgebase_id
    FROM section s2
    JOIN family
    ON s2.id = family.parent_section_id AND s2.organization_id = ?
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
        s1.parent_section_id,
        kbr.url_id,
        kbr.route
    FROM section s1
    INNER JOIN knowledgebase_route kbr
    ON kbr.section_id = s1.id AND kbr.is_canonical IS TRUE
    WHERE s1.id = :id AND s1.knowledgebase_id = :knowledgebase_id AND s1.organization_id = :organization_id
    UNION ALL
    SELECT
        s2.id,
        s2.name,
        s2.parent_section_id,
        kbr.url_id,
        kbr.route
    FROM section s2
    JOIN family
    ON s2.id = family.parent_section_id
    INNER JOIN knowledgebase_route kbr
    ON kbr.section_id = s2.id AND kbr.is_canonical IS TRUE
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

    public function getSectionAndDescendants(string $id, string $knowledgebaseId, string $organizationId): array
    {
        $sql = <<<SQL
WITH RECURSIVE tree AS (
    SELECT
        id,
        url_id,
        url_slug,
        parent_section_id,
        knowledgebase_id,
        organization_id,
        'section' AS type
    FROM section
    WHERE id = :id AND knowledgebase_id = :knowledgebase_id AND organization_id = :organization_id

    UNION ALL

    (
        SELECT
            child.id,
            child.url_id,
            child.url_slug,
            child.parent_section_id,
            child.knowledgebase_id,
            child.organization_id,
            child.type
        FROM
        (
            SELECT
                id,
                url_id,
                url_slug,
                section_id AS parent_section_id,
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
                parent_section_id,
                knowledgebase_id,
                organization_id,
                'section' AS type
            FROM section
            WHERE knowledgebase_id = :knowledgebase_id AND organization_id = :organization_id
        ) child, tree tree
        WHERE child.parent_section_id = tree.id
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
