<?php
declare(strict_types=1);

namespace Hipper\Document;

use Doctrine\DBAL\Connection;

class DocumentRepository
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
            ->from('document')
            ->andWhere('id = :id')
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'id' => $id,
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (empty($result)) {
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
            'document.id',
            'document.name',
            'document.description',
            'document.deduced_description',
            'document.updated',
            'route.route',
            'route.url_id',
        ];

        $qb = $this->connection->createQueryBuilder();
        $qb->select($fields)
            ->from('document', 'document')
            ->innerJoin(
                'document',
                'knowledgebase_route',
                'route',
                'route.document_id = document.id AND route.is_canonical IS TRUE'
            )
            ->andWhere('document.knowledgebase_id = :knowledgebase_id')
            ->andWhere('document.organization_id = :organization_id');

        if (null === $sectionId) {
            $qb->andWhere('document.section_id IS NULL');
        } else {
            $qb->andWhere('document.section_id = :section_id');
        }

        $qb->orderBy('document.created', 'DESC');

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
