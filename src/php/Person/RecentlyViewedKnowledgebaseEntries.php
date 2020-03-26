<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;

class RecentlyViewedKnowledgebaseEntries
{
    private KnowledgebaseRepository $knowledgebaseRepository;
    private PersonKnowledgebaseEntryViewRepository $entryViewRepository;
    private RecentlyViewedKnowledgebaseEntriesFormatter $formatter;

    public function __construct(
        KnowledgebaseRepository $knowledgebaseRepository,
        PersonKnowledgebaseEntryViewRepository $entryViewRepository,
        RecentlyViewedKnowledgebaseEntriesFormatter $formatter
    ) {
        $this->knowledgebaseRepository = $knowledgebaseRepository;
        $this->entryViewRepository = $entryViewRepository;
        $this->formatter = $formatter;
    }

    public function get(
        PersonModel $person,
        OrganizationModel $organization,
        string $displayTimeZone,
        ?string $returnTo,
        int $maxEntries = 5
    ): array {
        $entries = $this->entryViewRepository->getMostRecentlyViewedForPerson($person->getId(), $maxEntries);

        $knowledgebaseIds = $this->getKnowledgebaseIdsFromEntries($entries);
        $knowledgebasesResult = $this->knowledgebaseRepository->getKnowledgebaseOwnersForIds(
            $knowledgebaseIds,
            $organization->getId()
        );

        $knowledgebaseOwners = [
            $organization->getKnowledgebaseId() => $organization,
        ];
        foreach ($knowledgebasesResult as $row) {
            if ($row['entity'] === 'team') {
                $knowledgebaseOwners[$row['knowledgebase_id']] = TeamModel::createFromArray($row);
            }

            if ($row['entity'] === 'project') {
                $knowledgebaseOwners[$row['knowledgebase_id']] = ProjectModel::createFromArray($row);
            }
        }

        $formatted = $this->formatter->format(
            $organization,
            $knowledgebaseOwners,
            $displayTimeZone,
            $returnTo,
            $entries
        );

        return $formatted;
    }

    private function getKnowledgebaseIdsFromEntries(array $entries): array
    {
        $knowledgebaseIds = array_map(
            function ($entry) {
                return $entry['knowledgebase_id'];
            },
            $entries
        );
        return array_values(array_unique($knowledgebaseIds));
    }
}
