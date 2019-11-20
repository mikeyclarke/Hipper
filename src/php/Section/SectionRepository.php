<?php
declare(strict_types=1);

namespace Hipper\Section;

use Doctrine\DBAL\Connection;

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
}
