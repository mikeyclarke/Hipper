<?php
declare(strict_types=1);

namespace Hipper\Document;

use Doctrine\DBAL\Connection;

class DocumentRepository
{
    private const DEFAULT_FIELDS = [
        'id',
        'name',
        'description',
        'deduced_description',
        'content',
        'url_slug',
        'url_id',
        'knowledgebase_id',
        'organization_id',
        'topic_id',
        'created_by',
        'last_updated_by',
        'created',
        'updated',
    ];

    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function findById(string $id, string $organizationId): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select(self::DEFAULT_FIELDS)
            ->from('document')
            ->andWhere('id = :id')
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'id' => $id,
            'organization_id' => $organizationId,
        ]);

        $statementResult = $qb->execute();
        $result = $statementResult->fetchAssociative();

        if (empty($result)) {
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

        if (null === $topicId) {
            $qb->andWhere('document.topic_id IS NULL');
        } else {
            $qb->andWhere('document.topic_id = :topic_id');
        }

        $qb->orderBy('document.created', 'DESC');

        $qb->setParameters([
            'knowledgebase_id' => $knowledgebaseId,
            'topic_id' => $topicId,
            'organization_id' => $organizationId,
        ]);

        $statementResult = $qb->execute();
        $result = $statementResult->fetchAllAssociative();
        return $result;
    }
}
