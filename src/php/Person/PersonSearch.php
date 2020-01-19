<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;

class PersonSearch
{
    private PersonSearchRepository $personSearchRepository;
    private PersonSearchResultsFormatter $personSearchResultsFormatter;

    public function __construct(
        PersonSearchRepository $personSearchRepository,
        PersonSearchResultsFormatter $personSearchResultsFormatter
    ) {
        $this->personSearchRepository = $personSearchRepository;
        $this->personSearchResultsFormatter = $personSearchResultsFormatter;
    }

    public function search(
        string $searchQuery,
        string $displayTimeZone,
        OrganizationModel $organization
    ): array {
        $searchResults = $this->personSearchRepository->getResults($searchQuery, $organization->getId());
        return $this->personSearchResultsFormatter->format($organization, $displayTimeZone, $searchResults);
    }

    public function searchTeamMembers(
        string $searchQuery,
        string $displayTimeZone,
        OrganizationModel $organization,
        TeamModel $team
    ): array {
        $searchResults = $this->personSearchRepository->getResultsInTeam(
            $searchQuery,
            $organization->getId(),
            $team->getId()
        );
        return $this->personSearchResultsFormatter->format($organization, $displayTimeZone, $searchResults);
    }

    public function searchProjectMembers(
        string $searchQuery,
        string $displayTimeZone,
        OrganizationModel $organization,
        ProjectModel $project
    ): array {
        $searchResults = $this->personSearchRepository->getResultsInProject(
            $searchQuery,
            $organization->getId(),
            $project->getId()
        );
        return $this->personSearchResultsFormatter->format($organization, $displayTimeZone, $searchResults);
    }
}
