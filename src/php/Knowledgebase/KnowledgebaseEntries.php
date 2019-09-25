<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Document\DocumentRepository;
use Hipper\Section\SectionRepository;

class KnowledgebaseEntries
{
    private $documentRepository;
    private $sectionRepository;

    public function __construct(
        DocumentRepository $documentRepository,
        SectionRepository $sectionRepository
    ) {
        $this->documentRepository = $documentRepository;
        $this->sectionRepository = $sectionRepository;
    }

    public function get(string $knowledgebaseId, ?string $withinSectionId, string $organizationId): array
    {
        $docs = $this->documentRepository->getAllForKnowledgebaseInSection(
            $knowledgebaseId,
            $withinSectionId,
            $organizationId
        );
        $sections = $this->sectionRepository->getAllForKnowledgebaseInSection(
            $knowledgebaseId,
            $withinSectionId,
            $organizationId
        );

        return [$docs, $sections];
    }
}
