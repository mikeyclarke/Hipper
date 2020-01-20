<?php
declare(strict_types=1);

namespace Hipper\Tests\Team;

use Hipper\Organization\OrganizationModel;
use Hipper\Team\TeamSearchRepository;
use Hipper\Team\TeamSearchResultsFormatter;
use Hipper\Team\TeamSearch;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TeamSearchTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $teamSearchRepository;
    private $teamSearchResultsFormatter;
    private $teamSearch;

    public function setUp(): void
    {
        $this->teamSearchRepository = m::mock(TeamSearchRepository::class);
        $this->teamSearchResultsFormatter = m::mock(TeamSearchResultsFormatter::class);

        $this->teamSearch = new TeamSearch(
            $this->teamSearchRepository,
            $this->teamSearchResultsFormatter
        );
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

        $this->createTeamSearchRepositoryExpectation([$searchQuery, $organization->getId()], $searchResults);
        $this->createTeamSearchResultsFormatterExpectation(
            [$organization, $displayTimeZone, $searchResults],
            $formatted
        );

        $result = $this->teamSearch->search($searchQuery, $displayTimeZone, $organization);
        $this->assertEquals($formatted, $result);
    }

    private function createTeamSearchResultsFormatterExpectation($args, $result)
    {
        $this->teamSearchResultsFormatter
            ->shouldReceive('format')
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
}
