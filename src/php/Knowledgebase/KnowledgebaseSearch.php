<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Search\SearchResultsPaginatorFactory;
use Hipper\Section\SectionAncestory;
use Hipper\Team\TeamModel;

class KnowledgebaseSearch
{
    private KnowledgebaseRepository $knowledgebaseRepository;
    private KnowledgebaseSearchRepository $knowledgebaseSearchRepository;
    private KnowledgebaseSearchResultsFormatter $knowledgebaseSearchResultsFormatter;
    private SearchResultsPaginatorFactory $searchResultsPaginatorFactory;
    private SectionAncestory $sectionAncestory;

    public function __construct(
        KnowledgebaseRepository $knowledgebaseRepository,
        KnowledgebaseSearchRepository $knowledgebaseSearchRepository,
        KnowledgebaseSearchResultsFormatter $knowledgebaseSearchResultsFormatter,
        SearchResultsPaginatorFactory $searchResultsPaginatorFactory,
        SectionAncestory $sectionAncestory
    ) {
        $this->knowledgebaseRepository = $knowledgebaseRepository;
        $this->knowledgebaseSearchRepository = $knowledgebaseSearchRepository;
        $this->knowledgebaseSearchResultsFormatter = $knowledgebaseSearchResultsFormatter;
        $this->searchResultsPaginatorFactory = $searchResultsPaginatorFactory;
        $this->sectionAncestory = $sectionAncestory;
    }

    public function search(
        string $searchQuery,
        string $displayTimeZone,
        OrganizationModel $organization,
        int $numberOfPages = 1,
        int $startFromPage = 1
    ): array {
        $searchResultsPaginator = $this->searchResultsPaginatorFactory->create($numberOfPages, $startFromPage);
        $limit = $searchResultsPaginator->getLimit();
        $offset = $searchResultsPaginator->getOffset();

        $results = $this->knowledgebaseSearchRepository->getResults(
            $searchQuery,
            $organization->getId(),
            $limit,
            $offset
        );
        $moreResults = $searchResultsPaginator->hasMoreResults($results);
        $filteredResults = $searchResultsPaginator->filterResults($results);

        $parentSectionIds = $this->getParentSectionIdsFromResults($filteredResults);
        $ancestory = $this->sectionAncestory->getAncestorNamesForSectionIds(
            $parentSectionIds,
            $organization
        );

        $knowledgebaseIds = $this->getKnowledgebaseIdsFromResults($filteredResults);
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
            $ancestory,
            $displayTimeZone,
            $filteredResults
        );

        return [$formatted, $moreResults];
    }

    public function searchWithinKnowledgebase(
        string $searchQuery,
        string $displayTimeZone,
        OrganizationModel $organization,
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        int $numberOfPages = 1,
        int $startFromPage = 1
    ): array {
        $searchResultsPaginator = $this->searchResultsPaginatorFactory->create($numberOfPages, $startFromPage);
        $limit = $searchResultsPaginator->getLimit();
        $offset = $searchResultsPaginator->getOffset();

        $results = $this->knowledgebaseSearchRepository->getResultsInKnowledgebase(
            $searchQuery,
            $organization->getId(),
            $knowledgebaseOwner->getKnowledgebaseId(),
            $limit,
            $offset
        );
        $moreResults = $searchResultsPaginator->hasMoreResults($results);
        $filteredResults = $searchResultsPaginator->filterResults($results);

        $parentSectionIds = $this->getParentSectionIdsFromResults($filteredResults);
        $ancestory = $this->sectionAncestory->getAncestorNamesForSectionIds(
            $parentSectionIds,
            $organization
        );

        $knowledgebaseOwners = [];
        $knowledgebaseOwners[$knowledgebaseOwner->getKnowledgebaseId()] = $knowledgebaseOwner;

        $formatted = $this->knowledgebaseSearchResultsFormatter->format(
            $organization,
            $knowledgebaseOwners,
            $ancestory,
            $displayTimeZone,
            $filteredResults
        );
        return [$formatted, $moreResults];
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

    private function getParentSectionIdsFromResults(array $results): array
    {
        $sectionIds = [];
        foreach ($results as $result) {
            if (null !== $result['parent_section_id'] && !in_array($result['parent_section_id'], $sectionIds)) {
                $sectionIds[] = $result['parent_section_id'];
            }
        }
        return $sectionIds;
    }
}
