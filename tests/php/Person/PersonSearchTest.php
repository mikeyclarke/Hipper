<?php
declare(strict_types=1);

namespace Hipper\Tests\Person;

use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonSearch;
use Hipper\Person\PersonSearchRepository;
use Hipper\Person\PersonSearchResultsFormatter;
use Hipper\Project\ProjectModel;
use Hipper\Search\SearchResultsPaginator;
use Hipper\Search\SearchResultsPaginatorFactory;
use Hipper\Team\TeamModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PersonSearchTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $personSearchRepository;
    private $personSearchResultsFormatter;
    private $searchResultsPaginatorFactory;
    private $personSearch;
    private $searchResultsPaginator;

    public function setUp(): void
    {
        $this->personSearchRepository = m::mock(PersonSearchRepository::class);
        $this->personSearchResultsFormatter = m::mock(PersonSearchResultsFormatter::class);
        $this->searchResultsPaginatorFactory = m::mock(SearchResultsPaginatorFactory::class);

        $this->personSearch = new PersonSearch(
            $this->personSearchRepository,
            $this->personSearchResultsFormatter,
            $this->searchResultsPaginatorFactory
        );

        $this->searchResultsPaginator = m::mock(SearchResultsPaginator::class);
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

        $this->createSearchResultsPaginatorFactoryExpectation([1, 1]);
        $this->createSearchResultsPaginatorGetLimit(11);
        $this->createSearchResultsPaginatorGetOffset(0);
        $this->createPersonSearchRepositoryGetResultsExpectation(
            [$searchQuery, $organizationId, 11, 0],
            $searchResults
        );
        $this->createSearchResultsPaginatorHasMoreResultsExpectation([$searchResults], false);
        $this->createSearchResultsPaginatorFilterResultsExpectation([$searchResults], $searchResults);
        $this->createPersonSearchResultsFormatterExpectation(
            [$organization, $displayTimeZone, $searchResults],
            $formatted
        );

        $result = $this->personSearch->search($searchQuery, $displayTimeZone, $organization);
        $this->assertIsArray($result);
        $this->assertEquals($formatted, $result[0]);
        $this->assertFalse($result[1]);
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

        $this->createSearchResultsPaginatorFactoryExpectation([1, 1]);
        $this->createSearchResultsPaginatorGetLimit(11);
        $this->createSearchResultsPaginatorGetOffset(0);
        $this->createPersonSearchRepositoryGetResultsInTeamExpectation(
            [$searchQuery, $organizationId, $teamId, 11, 0],
            $searchResults
        );
        $this->createSearchResultsPaginatorHasMoreResultsExpectation([$searchResults], false);
        $this->createSearchResultsPaginatorFilterResultsExpectation([$searchResults], $searchResults);
        $this->createPersonSearchResultsFormatterExpectation(
            [$organization, $displayTimeZone, $searchResults],
            $formatted
        );

        $result = $this->personSearch->searchTeamMembers($searchQuery, $displayTimeZone, $organization, $team);
        $this->assertIsArray($result);
        $this->assertEquals($formatted, $result[0]);
        $this->assertFalse($result[1]);
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

        $this->createSearchResultsPaginatorFactoryExpectation([1, 1]);
        $this->createSearchResultsPaginatorGetLimit(11);
        $this->createSearchResultsPaginatorGetOffset(0);
        $this->createPersonSearchRepositoryGetResultsInProjectExpectation(
            [$searchQuery, $organizationId, $projectId, 11, 0],
            $searchResults
        );
        $this->createSearchResultsPaginatorHasMoreResultsExpectation([$searchResults], false);
        $this->createSearchResultsPaginatorFilterResultsExpectation([$searchResults], $searchResults);
        $this->createPersonSearchResultsFormatterExpectation(
            [$organization, $displayTimeZone, $searchResults],
            $formatted
        );

        $result = $this->personSearch->searchProjectMembers($searchQuery, $displayTimeZone, $organization, $project);
        $this->assertIsArray($result);
        $this->assertEquals($formatted, $result[0]);
        $this->assertFalse($result[1]);
    }

    private function createPersonSearchResultsFormatterExpectation($args, $result)
    {
        $this->personSearchResultsFormatter
            ->shouldReceive('format')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSearchResultsPaginatorFilterResultsExpectation($args, $result)
    {
        $this->searchResultsPaginator
            ->shouldReceive('filterResults')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSearchResultsPaginatorHasMoreResultsExpectation($args, $result)
    {
        $this->searchResultsPaginator
            ->shouldReceive('hasMoreResults')
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

    private function createSearchResultsPaginatorGetOffset($result)
    {
        $this->searchResultsPaginator
            ->shouldReceive('getOffset')
            ->once()
            ->andReturn($result);
    }

    private function createSearchResultsPaginatorGetLimit($result)
    {
        $this->searchResultsPaginator
            ->shouldReceive('getLimit')
            ->once()
            ->andReturn($result);
    }

    private function createSearchResultsPaginatorFactoryExpectation($args)
    {
        $this->searchResultsPaginatorFactory
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($this->searchResultsPaginator);
    }
}
