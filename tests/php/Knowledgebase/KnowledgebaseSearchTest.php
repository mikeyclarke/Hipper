<?php
declare(strict_types=1);

namespace Hipper\Tests\Knowledgebase;

use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseSearch;
use Hipper\Knowledgebase\KnowledgebaseSearchRepository;
use Hipper\Knowledgebase\KnowledgebaseSearchResultsFormatter;
use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Search\SearchResultsPaginator;
use Hipper\Search\SearchResultsPaginatorFactory;
use Hipper\Team\TeamModel;
use Hipper\Topic\TopicAncestory;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class KnowledgebaseSearchTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $knowledgebaseRepository;
    private $knowledgebaseSearchRepository;
    private $knowledgebaseSearchResultsFormatter;
    private $searchResultsPaginatorFactory;
    private $topicAncestory;
    private $knowledgebaseSearch;
    private $searchResultsPaginator;

    public function setUp(): void
    {
        $this->knowledgebaseRepository = m::mock(KnowledgebaseRepository::class);
        $this->knowledgebaseSearchRepository = m::mock(KnowledgebaseSearchRepository::class);
        $this->knowledgebaseSearchResultsFormatter = m::mock(KnowledgebaseSearchResultsFormatter::class);
        $this->searchResultsPaginatorFactory = m::mock(SearchResultsPaginatorFactory::class);
        $this->topicAncestory = m::mock(TopicAncestory::class);

        $this->knowledgebaseSearch = new KnowledgebaseSearch(
            $this->knowledgebaseRepository,
            $this->knowledgebaseSearchRepository,
            $this->knowledgebaseSearchResultsFormatter,
            $this->searchResultsPaginatorFactory,
            $this->topicAncestory
        );

        $this->searchResultsPaginator = m::mock(SearchResultsPaginator::class);
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
        $organization->setKnowledgebaseId('org-kb-uuid');

        $searchResults = [
            [
                'knowledgebase_id' => 'kb1-uuid',
                'parent_topic_id' => 'topic1-uuid',
            ],
            [
                'knowledgebase_id' => 'kb2-uuid',
                'parent_topic_id' => null,
            ],
            [
                'knowledgebase_id' => 'kb1-uuid',
                'parent_topic_id' => 'topic2-uuid',
            ],
        ];
        $parentTopicIds = ['topic1-uuid', 'topic2-uuid'];
        $ancestory = ['ancestory'];
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

        $this->createSearchResultsPaginatorFactoryExpectation([1, 1]);
        $this->createSearchResultsPaginatorGetLimitExpectation(11);
        $this->createSearchResultsPaginatorGetOffsetExpectation(0);
        $this->createKnowledgebaseSearchRepositoryGetResultsExpectation(
            [$searchQuery, $organization->getId(), 11, 0],
            $searchResults
        );
        $this->createSearchResultsPaginatorHasMoreResultsExpectation([$searchResults], false);
        $this->createSearchResultsPaginatorFilterResultsExpectation([$searchResults], $searchResults);
        $this->createTopicAncestoryExpectation([$parentTopicIds, $organization], $ancestory);
        $this->createKnowledgebaseRepositoryExpectation(
            [['kb1-uuid', 'kb2-uuid'], $organization->getId()],
            $knowledgebasesResult
        );
        $this->createKnowledgebaseSearchResultsFormatterExpectation(
            [
                $organization,
                [
                    'org-kb-uuid' => $organization,
                    'kb1-uuid' => TeamModel::createFromArray($knowledgebasesResult[0]),
                    'kb2-uuid' => ProjectModel::createFromArray($knowledgebasesResult[1]),
                ],
                $ancestory,
                $displayTimeZone,
                $searchResults
            ],
            $formattedResults
        );

        $result = $this->knowledgebaseSearch->search($searchQuery, $displayTimeZone, $organization);
        $this->assertIsArray($result);
        $this->assertEquals($formattedResults, $result[0]);
        $this->assertFalse($result[1]);
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
        $organization->setKnowledgebaseId('org-kb-uuid');
        $knowledgebaseOwner = new TeamModel;
        $knowledgebaseOwner->setKnowledgebaseId('team-kb-uuid');

        $searchResults = [
            [
                'knowledgebase_id' => 'kb1-uuid',
                'parent_topic_id' => 'topic1-uuid',
            ],
            [
                'knowledgebase_id' => 'kb2-uuid',
                'parent_topic_id' => null,
            ],
            [
                'knowledgebase_id' => 'kb1-uuid',
                'parent_topic_id' => 'topic2-uuid',
            ],
        ];
        $parentTopicIds = ['topic1-uuid', 'topic2-uuid'];
        $ancestory = ['ancestory'];
        $formattedResults = [
            ['formatted-result'],
            ['formatted-result'],
            ['formatted-result'],
        ];

        $this->createSearchResultsPaginatorFactoryExpectation([1, 1]);
        $this->createSearchResultsPaginatorGetLimitExpectation(11);
        $this->createSearchResultsPaginatorGetOffsetExpectation(0);
        $this->createKnowledgebaseSearchRepositoryGetResultsInKnowledgebaseExpectation(
            [$searchQuery, $organization->getId(), $knowledgebaseOwner->getKnowledgebaseId(), 11, 0],
            $searchResults
        );
        $this->createSearchResultsPaginatorHasMoreResultsExpectation([$searchResults], false);
        $this->createSearchResultsPaginatorFilterResultsExpectation([$searchResults], $searchResults);
        $this->createTopicAncestoryExpectation([$parentTopicIds, $organization], $ancestory);
        $this->createKnowledgebaseSearchResultsFormatterExpectation(
            [
                $organization,
                [$knowledgebaseOwner->getKnowledgebaseId() => $knowledgebaseOwner],
                $ancestory,
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
        $this->assertIsArray($result);
        $this->assertEquals($formattedResults, $result[0]);
        $this->assertFalse($result[1]);
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

    private function createTopicAncestoryExpectation($args, $result)
    {
        $this->topicAncestory
            ->shouldReceive('getAncestorNamesForTopicIds')
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
