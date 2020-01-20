<?php
declare(strict_types=1);

namespace Hipper\Project;

use Hipper\Organization\OrganizationModel;

class ProjectSearch
{
    private ProjectSearchRepository $projectSearchRepository;
    private ProjectSearchResultsFormatter $projectSearchResultsFormatter;

    public function __construct(
        ProjectSearchRepository $projectSearchRepository,
        ProjectSearchResultsFormatter $projectSearchResultsFormatter
    ) {
        $this->projectSearchRepository = $projectSearchRepository;
        $this->projectSearchResultsFormatter = $projectSearchResultsFormatter;
    }

    public function search(string $searchQuery, string $displayTimeZone, OrganizationModel $organization): array
    {
        $searchResults = $this->projectSearchRepository->getResults($searchQuery, $organization->getId());
        return $this->projectSearchResultsFormatter->format($organization, $displayTimeZone, $searchResults);
    }
}
