<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Document\DocumentRepository;
use Hipper\Topic\TopicRepository;

class KnowledgebaseEntries
{
    private $documentRepository;
    private $topicRepository;

    public function __construct(
        DocumentRepository $documentRepository,
        TopicRepository $topicRepository
    ) {
        $this->documentRepository = $documentRepository;
        $this->topicRepository = $topicRepository;
    }

    public function get(string $knowledgebaseId, ?string $withinTopicId, string $organizationId): array
    {
        $docs = $this->documentRepository->getAllForKnowledgebaseInTopic(
            $knowledgebaseId,
            $withinTopicId,
            $organizationId
        );
        $topics = $this->topicRepository->getAllForKnowledgebaseInTopic(
            $knowledgebaseId,
            $withinTopicId,
            $organizationId
        );

        return [$docs, $topics];
    }
}
