<?php
declare(strict_types=1);

namespace Hipper\Section;

use Hipper\Organization\OrganizationModel;
use RuntimeException;

class SectionAncestory
{
    private SectionRepository $sectionRepository;

    public function __construct(
        SectionRepository $sectionRepository
    ) {
        $this->sectionRepository = $sectionRepository;
    }

    public function getAncestorNamesForSectionIds(array $sectionIds, OrganizationModel $organization): array
    {
        if (empty($sectionIds)) {
            return [];
        }

        $sections = $this->sectionRepository->getNameAndAncestorNamesWithIds($sectionIds, $organization->getId());

        $indexedSections = [];
        foreach ($sections as $section) {
            $indexedSections[$section['id']] = $section;
        }

        $result = [];
        foreach ($sectionIds as $id) {
            $result[$id] = array_reverse($this->getNames($indexedSections, $id));
        }

        return $result;
    }

    private function getNames(array $indexedSections, string $id): array
    {
        if (!isset($indexedSections[$id])) {
            throw new RuntimeException(sprintf('Section “%s” not found', $id));
        }

        $section = $indexedSections[$id];

        $ancestorNames = [];
        if (null !== $section['parent_section_id']) {
            $ancestorNames = $this->getNames($indexedSections, $section['parent_section_id']);
        }

        return [$section['name'], ...$ancestorNames];
    }
}
