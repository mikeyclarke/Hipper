<?php
declare(strict_types=1);

namespace Hipper\Document;

use Doctrine\DBAL\Connection;

class DocumentRevisionRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function getHistoryForDocument(string $documentId, string $knowledgebaseId, string $organizationId): array
    {
        $qb = $this->connection->createQueryBuilder();
        $fields = [
            'revision.created',
            'person.id AS author_id',
            'person.name AS author_name',
            'person.abbreviated_name AS author_abbreviated_name',
        ];

        $qb->select($fields)
            ->from('document_revision', 'revision')
            ->leftJoin('revision', 'person', 'person', 'person.id = revision.created_by')
            ->andWhere('revision.document_id = :document_id')
            ->andWhere('revision.knowledgebase_id = :knowledgebase_id')
            ->andWhere('revision.organization_id = :organization_id');

        $qb->setParameters([
            'document_id' => $documentId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetchAll();

        return $result;
    }
}
