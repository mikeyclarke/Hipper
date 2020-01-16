<?php
declare(strict_types=1);

namespace Hipper\Tests\Knowledgebase;

use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseSearch;
use Hipper\Knowledgebase\KnowledgebaseSearchRepository;
use Hipper\Knowledgebase\KnowledgebaseSearchResultsFormatter;
use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class KnowledgebaseSearchTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $knowledgebaseRepository;
    private $knowledgebaseSearchRepository;
    private $knowledgebaseSearchResultsFormatter;
    private $knowledgebaseSearch;

    public function setUp(): void
    {
        $this->knowledgebaseRepository = m::mock(KnowledgebaseRepository::class);
        $this->knowledgebaseSearchRepository = m::mock(KnowledgebaseSearchRepository::class);
        $this->knowledgebaseSearchResultsFormatter = m::mock(KnowledgebaseSearchResultsFormatter::class);

        $this->knowledgebaseSearch = new KnowledgebaseSearch(
            $this->knowledgebaseRepository,
            $this->knowledgebaseSearchRepository,
            $this->knowledgebaseSearchResultsFormatter
        );
    }

    /**
     * @test
     */
    public function search()
    {
        $searchQuery = 'some search term';
        $displayTimeZone = 'Europe/London';
        $organization = new OrganizationModel;
        $organization->setId('org-uuid');

        $searchResults = [
            [
                'knowledgebase_id' => 'kb1-uuid',
            ],
            [
                'knowledgebase_id' => 'kb2-uuid',
            ],
            [
                'knowledgebase_id' => 'kb1-uuid',
            ],
        ];
        $knowledgebasesResult = [
            [
                'entity' => 'team',
                'knowledgebase_id' => 'kb1-uuid',
            ],
            [
                'entity' => 'project',
                'knowledgebase_id' => 'kb2-uuid',
            ]
        ];
        $formattedResults = [
            ['formatted-result'],
            ['formatted-result'],
            ['formatted-result'],
        ];

        $this->createKnowledgebaseSearchRepositoryGetResultsExpectation(
            [$searchQuery, $organization->getId()],
            $searchResults
        );
        $this->createKnowledgebaseRepositoryExpectation(
            [['kb1-uuid', 'kb2-uuid'], $organization->getId()],
            $knowledgebasesResult
        );
        $this->createKnowledgebaseSearchResultsFormatterExpectation(
            [
                $organization,
                [
                    'kb1-uuid' => TeamModel::createFromArray($knowledgebasesResult[0]),
                    'kb2-uuid' => ProjectModel::createFromArray($knowledgebasesResult[1]),
                ],
                $displayTimeZone,
                $searchResults
            ],
            $formattedResults
        );

        $result = $this->knowledgebaseSearch->search($searchQuery, $displayTimeZone, $organization);
        $this->assertEquals($formattedResults, $result);
    }

    /**
     * @test
     */
    public function searchWithinKnowledgebase()
    {
        $searchQuery = 'some search term';
        $displayTimeZone = 'Europe/London';
        $organization = new OrganizationModel;
        $organization->setId('org-uuid');
        $knowledgebaseOwner = new TeamModel;
        $knowledgebaseOwner->setKnowledgebaseId('team-kb-uuid');

        $searchResults = [
            ['result'],
            ['result'],
            ['result'],
        ];
        $formattedResults = [
            ['formatted-result'],
            ['formatted-result'],
            ['formatted-result'],
        ];

        $this->createKnowledgebaseSearchRepositoryGetResultsInKnowledgebaseExpectation(
            [$searchQuery, $organization->getId(), $knowledgebaseOwner->getKnowledgebaseId()],
            $searchResults
        );
        $this->createKnowledgebaseSearchResultsFormatterExpectation(
            [
                $organization,
                [$knowledgebaseOwner->getKnowledgebaseId() => $knowledgebaseOwner],
                $displayTimeZone,
                $searchResults
            ],
            $formattedResults
        );

        $result = $this->knowledgebaseSearch->searchWithinKnowledgebase(
            $searchQuery,
            $displayTimeZone,
            $organization,
            $knowledgebaseOwner
        );
        $this->assertEquals($formattedResults, $result);
    }

    private function createKnowledgebaseSearchResultsFormatterExpectation($args, $result)
    {
        $this->knowledgebaseSearchResultsFormatter
            ->shouldReceive('format')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseRepositoryExpectation($args, $result)
    {
        $this->knowledgebaseRepository
            ->shouldReceive('getKnowledgebaseOwnersForIds')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseSearchRepositoryGetResultsInKnowledgebaseExpectation($args, $result)
    {
        $this->knowledgebaseSearchRepository
            ->shouldReceive('getResultsInKnowledgebase')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseSearchRepositoryGetResultsExpectation($args, $result)
    {
        $this->knowledgebaseSearchRepository
            ->shouldReceive('getResults')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
