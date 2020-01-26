<?php
declare(strict_types=1);

namespace Hipper\Team;

use Hipper\Organization\OrganizationModel;
use Hipper\Search\SearchResultsPaginatorFactory;

class TeamSearch
{
    private SearchResultsPaginatorFactory $searchResultsPaginatorFactory;
    private TeamSearchRepository $teamSearchRepository;
    private TeamSearchResultsFormatter $teamSearchResultsFormatter;

    public function __construct(
        SearchResultsPaginatorFactory $searchResultsPaginatorFactory,
        TeamSearchRepository $teamSearchRepository,
        TeamSearchResultsFormatter $teamSearchResultsFormatter
    ) {
        $this->searchResultsPaginatorFactory = $searchResultsPaginatorFactory;
        $this->teamSearchRepository = $teamSearchRepository;
        $this->teamSearchResultsFormatter = $teamSearchResultsFormatter;
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

        $searchResults = $this->teamSearchRepository->getResults($searchQuery, $organization->getId(), $limit, $offset);

        $moreResults = $searchResultsPaginator->hasMoreResults($searchResults);
        $filteredResults = $searchResultsPaginator->filterResults($searchResults);

        $formattedResults = $this->teamSearchResultsFormatter->format(
            $organization,
            $displayTimeZone,
            $filteredResults
        );

        return [$formattedResults, $moreResults];
    }
}
