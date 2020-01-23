<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;

class KnowledgebaseSearch
{
    private const RESULTS_PER_PAGE = 10;

    private KnowledgebaseRepository $knowledgebaseRepository;
    private KnowledgebaseSearchRepository $knowledgebaseSearchRepository;
    private KnowledgebaseSearchResultsFormatter $knowledgebaseSearchResultsFormatter;

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
        OrganizationModel $organization,
        int $numberOfPages = 1,
        int $startFromPage = 1
    ): array {
        $moreResults = false;
        $limit = (self::RESULTS_PER_PAGE * $numberOfPages) + 1;
        $offset = self::RESULTS_PER_PAGE * ($startFromPage - 1);

        $results = $this->knowledgebaseSearchRepository->getResults(
            $searchQuery,
            $organization->getId(),
            $limit,
            $offset
        );
        if (count($results) === $limit) {
            array_pop($results);
            $moreResults = true;
        }

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

        $formatted = $this->knowledgebaseSearchResultsFormatter->format(
            $organization,
            $knowledgebaseOwners,
            $displayTimeZone,
            $results
        );

        return [$formatted, $moreResults];
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
