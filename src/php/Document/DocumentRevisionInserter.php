<?php
declare(strict_types=1);

namespace Lithos\Document;

use Doctrine\DBAL\Connection;

class DocumentRevisionInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(
        string $id,
        string $documentId,
        string $name,
        string $organizationId,
        string $knowledgebaseId,
        string $createdBy,
        string $description = null,
        string $deducedDescription = null,
        string $content = null
    ): void {
        $this->connection->insert(
            'document_revision',
            [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'deduced_description' => $deducedDescription,
                'content' => $content,
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'document_id' => $documentId,
                'created_by' => $createdBy,
            ]
        );
    }
}
