<?php
declare(strict_types=1);

namespace Hipper\Activity\Storage;

use Doctrine\DBAL\Connection;

class ActivityInserter
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(
        string $id,
        string $type,
        string $actorId,
        string $organizationId,
        ?string $storage = null,
        ?string $documentId = null,
        ?string $topicId = null,
        ?string $teamId = null,
        ?string $projectId = null
    ): void {
        $this->connection->insert(
            'activity',
            [
                'id' => $id,
                'type' => $type,
                'storage' => $storage,
                'document_id' => $documentId,
                'topic_id' => $topicId,
                'team_id' => $teamId,
                'project_id' => $projectId,
                'organization_id' => $organizationId,
                'actor_id' => $actorId,
            ]
        );
    }
}
