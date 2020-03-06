<?php
declare(strict_types=1);

namespace Hipper\Activity;

use Hipper\Activity\Storage\ActivityInserter;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Person\PersonModel;

class ActivityCreator
{
    private ActivityInserter $activityInserter;
    private IdGenerator $idGenerator;

    public function __construct(
        ActivityInserter $activityInserter,
        IdGenerator $idGenerator
    ) {
        $this->activityInserter = $activityInserter;
        $this->idGenerator = $idGenerator;
    }

    public function create(
        PersonModel $actor,
        string $type,
        array $properties = [],
        ?string $documentId = null,
        ?string $topicId = null,
        ?string $teamId = null,
        ?string $projectId = null
    ): void {
        $id = $this->idGenerator->generate();
        $storage = null;

        if (!empty($properties)) {
            $storage = json_encode($properties, JSON_THROW_ON_ERROR);
        }

        $this->activityInserter->insert(
            $id,
            $type,
            $actor->getId(),
            $actor->getOrganizationId(),
            $storage,
            $documentId,
            $topicId,
            $teamId,
            $projectId
        );
    }
}
