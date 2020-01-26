<?php
declare(strict_types=1);

namespace Hipper\Project;

use Hipper\Organization\OrganizationModel;
use Hipper\Search\SearchResultsPaginatorFactory;

class ProjectSearch
{
    private ProjectSearchRepository $projectSearchRepository;
    private ProjectSearchResultsFormatter $projectSearchResultsFormatter;
    private SearchResultsPaginatorFactory $searchResultsPaginatorFactory;

    public function __construct(
        ProjectSearchRepository $projectSearchRepository,
        ProjectSearchResultsFormatter $projectSearchResultsFormatter,
        SearchResultsPaginatorFactory $searchResultsPaginatorFactory
    ) {
        $this->projectSearchRepository = $projectSearchRepository;
        $this->projectSearchResultsFormatter = $projectSearchResultsFormatter;
        $this->searchResultsPaginatorFactory = $searchResultsPaginatorFactory;
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

        $searchResults = $this->projectSearchRepository->getResults(
            $searchQuery,
            $organization->getId(),
            $limit,
            $offset
        );

        $moreResults = $searchResultsPaginator->hasMoreResults($searchResults);
        $filteredResults = $searchResultsPaginator->filterResults($searchResults);

        $formattedResults = $this->projectSearchResultsFormatter->format(
            $organization,
            $displayTimeZone,
            $filteredResults
        );

        return [$formattedResults, $moreResults];
    }
}
