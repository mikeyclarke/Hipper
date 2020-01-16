<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;

class KnowledgebaseSearch
{
    private $knowledgebaseRepository;
    private $knowledgebaseSearchRepository;
    private $knowledgebaseSearchResultsFormatter;

    public function __construct(
        KnowledgebaseRepository $knowledgebaseRepository,
        KnowledgebaseSearchRepository $knowledgebaseSearchRepository,
        KnowledgebaseSearchResultsFormatter $knowledgebaseSearchResultsFormatter
    ) {
        $this->knowledgebaseRepository = $knowledgebaseRepository;
        $this->knowledgebaseSearchRepository = $knowledgebaseSearchRepository;
        $this->knowledgebaseSearchResultsFormatter = $knowledgebaseSearchResultsFormatter;
    }

    public function search(
        string $searchQuery,
        string $displayTimeZone,
        OrganizationModel $organization
    ): array {
        $results = $this->knowledgebaseSearchRepository->getResults($searchQuery, $organization->getId());

        $knowledgebaseIds = $this->getKnowledgebaseIdsFromResults($results);
        $knowledgebasesResult = $this->knowledgebaseRepository->getKnowledgebaseOwnersForIds(
            $knowledgebaseIds,
            $organization->getId()
        );

        $knowledgebaseOwners = [];
        foreach ($knowledgebasesResult as $row) {
            if ($row['entity'] === 'team') {
                $knowledgebaseOwners[$row['knowledgebase_id']] = TeamModel::createFromArray($row);
            }

            if ($row['entity'] === 'project') {
                $knowledgebaseOwners[$row['knowledgebase_id']] = ProjectModel::createFromArray($row);
            }
        }

        return $this->knowledgebaseSearchResultsFormatter->format(
            $organization,
            $knowledgebaseOwners,
            $displayTimeZone,
            $results
        );
    }

    public function searchWithinKnowledgebase(
        string $searchQuery,
        string $displayTimeZone,
        OrganizationModel $organization,
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner
    ): array {
        $results = $this->knowledgebaseSearchRepository->getResultsInKnowledgebase(
            $searchQuery,
            $organization->getId(),
            $knowledgebaseOwner->getKnowledgebaseId()
        );

        $knowledgebaseOwners = [];
        $knowledgebaseOwners[$knowledgebaseOwner->getKnowledgebaseId()] = $knowledgebaseOwner;

        return $this->knowledgebaseSearchResultsFormatter->format(
            $organization,
            $knowledgebaseOwners,
            $displayTimeZone,
            $results
        );
    }

    private function getKnowledgebaseIdsFromResults(array $results): array
    {
        $knowledgebaseIds = array_map(
            function ($result) {
                return $result['knowledgebase_id'];
            },
            $results
        );
        return array_unique($knowledgebaseIds);
    }
}
