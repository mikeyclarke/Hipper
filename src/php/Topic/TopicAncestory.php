<?php
declare(strict_types=1);

namespace Hipper\Topic;

use Hipper\Organization\OrganizationModel;
use RuntimeException;

class TopicAncestory
{
    private TopicRepository $topicRepository;

    public function __construct(
        TopicRepository $topicRepository
    ) {
        $this->topicRepository = $topicRepository;
    }

    public function getAncestorNamesForTopicIds(array $topicIds, OrganizationModel $organization): array
    {
        if (empty($topicIds)) {
            return [];
        }

        $topics = $this->topicRepository->getNameAndAncestorNamesWithIds($topicIds, $organization->getId());

        $indexedTopics = [];
        foreach ($topics as $topic) {
            $indexedTopics[$topic['id']] = $topic;
        }

        $result = [];
        foreach ($topicIds as $id) {
            $result[$id] = array_reverse($this->getNames($indexedTopics, $id));
        }

        return $result;
    }

    private function getNames(array $indexedTopics, string $id): array
    {
        if (!isset($indexedTopics[$id])) {
            throw new RuntimeException(sprintf('Topic “%s” not found', $id));
        }

        $topic = $indexedTopics[$id];

        $ancestorNames = [];
        if (null !== $topic['parent_topic_id']) {
            $ancestorNames = $this->getNames($indexedTopics, $topic['parent_topic_id']);
        }

        return [$topic['name'], ...$ancestorNames];
    }
}
