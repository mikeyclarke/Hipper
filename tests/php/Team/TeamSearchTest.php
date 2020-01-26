<?php
declare(strict_types=1);

namespace Hipper\Tests\Team;

use Hipper\Organization\OrganizationModel;
use Hipper\Search\SearchResultsPaginator;
use Hipper\Search\SearchResultsPaginatorFactory;
use Hipper\Team\TeamSearchRepository;
use Hipper\Team\TeamSearchResultsFormatter;
use Hipper\Team\TeamSearch;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TeamSearchTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $searchResultsPaginatorFactory;
    private $teamSearchRepository;
    private $teamSearchResultsFormatter;
    private $teamSearch;
    private $searchResultsPaginator;

    public function setUp(): void
    {
        $this->searchResultsPaginatorFactory = m::mock(SearchResultsPaginatorFactory::class);
        $this->teamSearchRepository = m::mock(TeamSearchRepository::class);
        $this->teamSearchResultsFormatter = m::mock(TeamSearchResultsFormatter::class);

        $this->teamSearch = new TeamSearch(
            $this->searchResultsPaginatorFactory,
            $this->teamSearchRepository,
            $this->teamSearchResultsFormatter
        );

        $this->searchResultsPaginator = m::mock(SearchResultsPaginator::class);
    }

    /**
     * @test
     */
    public function search()
    {
        $searchQuery = 'Engineering';
        $displayTimeZone = 'Europe/London';
        $organizationId = 'org-uuid';
        $organization = new OrganizationModel;
        $organization->setId($organizationId);

        $searchResults = ['search-results'];
        $formatted = ['formatted'];

        $this->createSearchResultsPaginatorFactoryExpectation([1, 1]);
        $this->createSearchResultsPaginatorGetLimitExpectation(11);
        $this->createSearchResultsPaginatorGetOffsetExpectation(0);
        $this->createTeamSearchRepositoryExpectation([$searchQuery, $organization->getId(), 11, 0], $searchResults);
        $this->createSearchResultsPaginatorHasMoreResultsExpectation([$searchResults], false);
        $this->createSearchResultsPaginatorFilterResultsExpectation([$searchResults], $searchResults);
        $this->createTeamSearchResultsFormatterExpectation(
            [$organization, $displayTimeZone, $searchResults],
            $formatted
        );

        $result = $this->teamSearch->search($searchQuery, $displayTimeZone, $organization);
        $this->assertIsArray($result);
        $this->assertEquals($formatted, $result[0]);
        $this->assertFalse($result[1]);
    }

    private function createTeamSearchResultsFormatterExpectation($args, $result)
    {
        $this->teamSearchResultsFormatter
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

    private function createTeamSearchRepositoryExpectation($args, $result)
    {
        $this->teamSearchRepository
            ->shouldReceive('getResults')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSearchResultsPaginatorGetOffsetExpectation($result)
    {
        $this->searchResultsPaginator
            ->shouldReceive('getOffset')
            ->once()
            ->andReturn($result);
    }

    private function createSearchResultsPaginatorGetLimitExpectation($result)
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
