<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Organization\OrganizationModel;
use Hipper\Topic\TopicRepository;

class KnowledgebaseBreadcrumbs
{
    private KnowledgebaseBreadcrumbsFormatter $formatter;
    private TopicRepository $topicRepository;

    public function __construct(
        KnowledgebaseBreadcrumbsFormatter $formatter,
        TopicRepository $topicRepository
    ) {
        $this->formatter = $formatter;
        $this->topicRepository = $topicRepository;
    }

    public function get(
        OrganizationModel $organization,
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        string $currentEntryName,
        ?string $parentTopicId
    ): array {
        $ancestorTopics = [];

        if (null !== $parentTopicId) {
            $ancestorTopics = $this->topicRepository->getByIdWithAncestors(
                $parentTopicId,
                $knowledgebaseOwner->getKnowledgebaseId(),
                $knowledgebaseOwner->getOrganizationId()
            );
        }

        return $this->formatter->format(
            $organization,
            $knowledgebaseOwner,
            array_reverse($ancestorTopics),
            $currentEntryName
        );
    }
}
