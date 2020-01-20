<?php
declare(strict_types=1);

namespace Hipper\Team;

use Hipper\Organization\OrganizationModel;

class TeamSearch
{
    private TeamSearchRepository $teamSearchRepository;
    private TeamSearchResultsFormatter $teamSearchResultsFormatter;

    public function __construct(
        TeamSearchRepository $teamSearchRepository,
        TeamSearchResultsFormatter $teamSearchResultsFormatter
    ) {
        $this->teamSearchRepository = $teamSearchRepository;
        $this->teamSearchResultsFormatter = $teamSearchResultsFormatter;
    }

    public function search(string $searchQuery, string $displayTimeZone, OrganizationModel $organization): array
    {
        $searchResults = $this->teamSearchRepository->getResults($searchQuery, $organization->getId());
        return $this->teamSearchResultsFormatter->format($organization, $displayTimeZone, $searchResults);
    }
}
