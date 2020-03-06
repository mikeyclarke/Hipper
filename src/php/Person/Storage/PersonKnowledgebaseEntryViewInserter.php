<?php
declare(strict_types=1);

namespace Hipper\Person\Storage;

use Doctrine\DBAL\Connection;

class PersonKnowledgebaseEntryViewInserter
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(
        string $id,
        string $personId,
        string $knowledgebaseId,
        string $organizationId,
        ?string $documentId,
        ?string $topicId
    ): void {
        $this->connection->insert(
            'person_knowledgebase_entry_view',
            [
                'id' => $id,
                'person_id' => $personId,
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'document_id' => $documentId,
                'topic_id' => $topicId,
            ]
        );
    }
}
