<?php
declare(strict_types=1);

namespace Hipper\Tests\Project;

use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectSearch;
use Hipper\Project\ProjectSearchRepository;
use Hipper\Project\ProjectSearchResultsFormatter;
use Hipper\Search\SearchResultsPaginator;
use Hipper\Search\SearchResultsPaginatorFactory;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ProjectSearchTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $projectSearchRepository;
    private $projectSearchResultsFormatter;
    private $searchResultsPaginatorFactory;
    private $projectSearch;
    private $searchResultsPaginator;

    public function setUp(): void
    {
        $this->projectSearchRepository = m::mock(ProjectSearchRepository::class);
        $this->projectSearchResultsFormatter = m::mock(ProjectSearchResultsFormatter::class);
        $this->searchResultsPaginatorFactory = m::mock(SearchResultsPaginatorFactory::class);

        $this->projectSearch = new ProjectSearch(
            $this->projectSearchRepository,
            $this->projectSearchResultsFormatter,
            $this->searchResultsPaginatorFactory
        );

        $this->searchResultsPaginator = m::mock(SearchResultsPaginator::class);
    }

    /**
     * @test
     */
    public function search()
    {
        $searchQuery = 'Marketing website';
        $displayTimeZone = 'Europe/London';
        $organizationId = 'org-uuid';
        $organization = new OrganizationModel;
        $organization->setId($organizationId);

        $searchResults = ['search-results'];
        $formatted = ['formatted'];

        $this->createSearchResultsPaginatorFactoryExpectation([1, 1]);
        $this->createSearchResultsPaginatorGetLimitExpectation(11);
        $this->createSearchResultsPaginatorGetOffsetExpectation(0);
        $this->createProjectSearchRepositoryExpectation([$searchQuery, $organizationId, 11, 0], $searchResults);
        $this->createSearchResultsPaginatorHasMoreResultsExpectation([$searchResults], false);
        $this->createSearchResultsPaginatorFilterResultsExpectation([$searchResults], $searchResults);
        $this->createProjectSearchResultsFormatter([$organization, $displayTimeZone, $searchResults], $formatted);

        $result = $this->projectSearch->search($searchQuery, $displayTimeZone, $organization);
        $this->assertIsArray($result);
        $this->assertEquals($formatted, $result[0]);
        $this->assertFalse($result[1]);
    }

    private function createProjectSearchResultsFormatter($args, $result)
    {
        $this->projectSearchResultsFormatter
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

    private function createProjectSearchRepositoryExpectation($args, $result)
    {
        $this->projectSearchRepository
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
