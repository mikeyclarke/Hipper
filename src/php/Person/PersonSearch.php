<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Search\SearchResultsPaginatorFactory;
use Hipper\Team\TeamModel;

class PersonSearch
{
    private PersonSearchRepository $personSearchRepository;
    private PersonSearchResultsFormatter $personSearchResultsFormatter;
    private SearchResultsPaginatorFactory $searchResultsPaginatorFactory;

    public function __construct(
        PersonSearchRepository $personSearchRepository,
        PersonSearchResultsFormatter $personSearchResultsFormatter,
        SearchResultsPaginatorFactory $searchResultsPaginatorFactory
    ) {
        $this->personSearchRepository = $personSearchRepository;
        $this->personSearchResultsFormatter = $personSearchResultsFormatter;
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

        $searchResults = $this->personSearchRepository->getResults(
            $searchQuery,
            $organization->getId(),
            $limit,
            $offset
        );
        $moreResults = $searchResultsPaginator->hasMoreResults($searchResults);
        $filteredResults = $searchResultsPaginator->filterResults($searchResults);

        $formatted = $this->personSearchResultsFormatter->format($organization, $displayTimeZone, $filteredResults);

        return [$formatted, $moreResults];
    }

    public function searchTeamMembers(
        string $searchQuery,
        string $displayTimeZone,
        OrganizationModel $organization,
        TeamModel $team,
        int $numberOfPages = 1,
        int $startFromPage = 1
    ): array {
        $searchResultsPaginator = $this->searchResultsPaginatorFactory->create($numberOfPages, $startFromPage);
        $limit = $searchResultsPaginator->getLimit();
        $offset = $searchResultsPaginator->getOffset();

        $searchResults = $this->personSearchRepository->getResultsInTeam(
            $searchQuery,
            $organization->getId(),
            $team->getId(),
            $limit,
            $offset
        );
        $moreResults = $searchResultsPaginator->hasMoreResults($searchResults);
        $filteredResults = $searchResultsPaginator->filterResults($searchResults);

        $formatted = $this->personSearchResultsFormatter->format($organization, $displayTimeZone, $filteredResults);

        return [$formatted, $moreResults];
    }

    public function searchProjectMembers(
        string $searchQuery,
        string $displayTimeZone,
        OrganizationModel $organization,
        ProjectModel $project,
        int $numberOfPages = 1,
        int $startFromPage = 1
    ): array {
        $searchResultsPaginator = $this->searchResultsPaginatorFactory->create($numberOfPages, $startFromPage);
        $limit = $searchResultsPaginator->getLimit();
        $offset = $searchResultsPaginator->getOffset();

        $searchResults = $this->personSearchRepository->getResultsInProject(
            $searchQuery,
            $organization->getId(),
            $project->getId(),
            $limit,
            $offset
        );
        $moreResults = $searchResultsPaginator->hasMoreResults($searchResults);
        $filteredResults = $searchResultsPaginator->filterResults($searchResults);

        $formatted = $this->personSearchResultsFormatter->format($organization, $displayTimeZone, $filteredResults);

        return [$formatted, $moreResults];
    }
}
