<?php
declare(strict_types=1);

namespace Hipper\Tests\Person;

use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonSearch;
use Hipper\Person\PersonSearchRepository;
use Hipper\Person\PersonSearchResultsFormatter;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PersonSearchTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $personSearchRepository;
    private $personSearchResultsFormatter;
    private $personSearch;

    public function setUp(): void
    {
        $this->personSearchRepository = m::mock(PersonSearchRepository::class);
        $this->personSearchResultsFormatter = m::mock(PersonSearchResultsFormatter::class);

        $this->personSearch = new PersonSearch(
            $this->personSearchRepository,
            $this->personSearchResultsFormatter
        );
    }

    /**
     * @test
     */
    public function search()
    {
        $searchQuery = 'James Bond';
        $displayTimeZone = 'Europe\London';
        $organizationId = 'org-uuid';
        $organization = new OrganizationModel;
        $organization->setId($organizationId);

        $searchResults = ['search-results'];
        $formatted = ['formatted-search-results'];

        $this->createPersonSearchRepositoryGetResultsExpectation([$searchQuery, $organizationId], $searchResults);
        $this->createPersonSearchResultsFormatterExpectation(
            [$organization, $displayTimeZone, $searchResults],
            $formatted
        );

        $result = $this->personSearch->search($searchQuery, $displayTimeZone, $organization);
        $this->assertEquals($formatted, $result);
    }

    /**
     * @test
     */
    public function searchTeamMembers()
    {
        $searchQuery = 'James Bond';
        $displayTimeZone = 'Europe\London';
        $organizationId = 'org-uuid';
        $organization = new OrganizationModel;
        $organization->setId($organizationId);
        $teamId = 'team-uuid';
        $team = new TeamModel;
        $team->setId($teamId);

        $searchResults = ['search-results'];
        $formatted = ['formatted-search-results'];

        $this->createPersonSearchRepositoryGetResultsInTeamExpectation(
            [$searchQuery, $organizationId, $teamId],
            $searchResults
        );
        $this->createPersonSearchResultsFormatterExpectation(
            [$organization, $displayTimeZone, $searchResults],
            $formatted
        );

        $result = $this->personSearch->searchTeamMembers($searchQuery, $displayTimeZone, $organization, $team);
        $this->assertEquals($formatted, $result);
    }

    /**
     * @test
     */
    public function searchProjectMembers()
    {
        $searchQuery = 'James Bond';
        $displayTimeZone = 'Europe\London';
        $organizationId = 'org-uuid';
        $organization = new OrganizationModel;
        $organization->setId($organizationId);
        $projectId = 'project-uuid';
        $project = new ProjectModel;
        $project->setId($projectId);

        $searchResults = ['search-results'];
        $formatted = ['formatted-search-results'];

        $this->createPersonSearchRepositoryGetResultsInProjectExpectation(
            [$searchQuery, $organizationId, $projectId],
            $searchResults
        );
        $this->createPersonSearchResultsFormatterExpectation(
            [$organization, $displayTimeZone, $searchResults],
            $formatted
        );

        $result = $this->personSearch->searchProjectMembers($searchQuery, $displayTimeZone, $organization, $project);
        $this->assertEquals($formatted, $result);
    }

    private function createPersonSearchResultsFormatterExpectation($args, $result)
    {
        $this->personSearchResultsFormatter
            ->shouldReceive('format')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createPersonSearchRepositoryGetResultsInProjectExpectation($args, $result)
    {
        $this->personSearchRepository
            ->shouldReceive('getResultsInProject')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createPersonSearchRepositoryGetResultsInTeamExpectation($args, $result)
    {
        $this->personSearchRepository
            ->shouldReceive('getResultsInTeam')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createPersonSearchRepositoryGetResultsExpectation($args, $result)
    {
        $this->personSearchRepository
            ->shouldReceive('getResults')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
