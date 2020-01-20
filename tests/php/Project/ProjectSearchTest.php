<?php
declare(strict_types=1);

namespace Hipper\Tests\Project;

use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectSearch;
use Hipper\Project\ProjectSearchRepository;
use Hipper\Project\ProjectSearchResultsFormatter;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ProjectSearchTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $projectSearchRepository;
    private $projectSearchResultsFormatter;
    private $projectSearch;

    public function setUp(): void
    {
        $this->projectSearchRepository = m::mock(ProjectSearchRepository::class);
        $this->projectSearchResultsFormatter = m::mock(ProjectSearchResultsFormatter::class);

        $this->projectSearch = new ProjectSearch(
            $this->projectSearchRepository,
            $this->projectSearchResultsFormatter
        );
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

        $this->createProjectSearchRepositoryExpectation([$searchQuery, $organizationId], $searchResults);
        $this->createProjectSearchResultsFormatter([$organization, $displayTimeZone, $searchResults], $formatted);

        $result = $this->projectSearch->search($searchQuery, $displayTimeZone, $organization);
        $this->assertEquals($formatted, $result);
    }

    private function createProjectSearchResultsFormatter($args, $result)
    {
        $this->projectSearchResultsFormatter
            ->shouldReceive('format')
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
}
