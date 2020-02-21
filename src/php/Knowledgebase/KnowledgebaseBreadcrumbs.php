<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Organization\OrganizationModel;
use Hipper\Section\SectionRepository;

class KnowledgebaseBreadcrumbs
{
    private KnowledgebaseBreadcrumbsFormatter $formatter;
    private SectionRepository $sectionRepository;

    public function __construct(
        KnowledgebaseBreadcrumbsFormatter $formatter,
        SectionRepository $sectionRepository
    ) {
        $this->formatter = $formatter;
        $this->sectionRepository = $sectionRepository;
    }

    public function get(
        OrganizationModel $organization,
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        string $currentEntryName,
        ?string $parentSectionId
    ): array {
        $ancestorSections = [];

        if (null !== $parentSectionId) {
            $ancestorSections = $this->sectionRepository->getByIdWithAncestors(
                $parentSectionId,
                $knowledgebaseOwner->getKnowledgebaseId(),
                $knowledgebaseOwner->getOrganizationId()
            );
        }

        return $this->formatter->format(
            $organization,
            $knowledgebaseOwner,
            array_reverse($ancestorSections),
            $currentEntryName
        );
    }
}
