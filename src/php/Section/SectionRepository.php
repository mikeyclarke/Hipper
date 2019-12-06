<?php
declare(strict_types=1);

namespace Hipper\Section;

use Doctrine\DBAL\Connection;
use PDO;

class SectionRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function findById(string $sectionId, string $knowledgebaseId, string $organizationId): ?array
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
}
