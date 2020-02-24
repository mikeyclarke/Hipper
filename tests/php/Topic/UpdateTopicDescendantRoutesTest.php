<?php
declare(strict_types=1);

namespace Hipper\Tests\Topic;

use Doctrine\DBAL\Connection;
use Hipper\Document\DocumentModel;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Topic\TopicModel;
use Hipper\Topic\TopicRepository;
use Hipper\Topic\UpdateTopicDescendantRoutes;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class UpdateTopicDescendantRoutesTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $knowledgebaseRoute;
    private $topicRepository;
    private $updateTopicDescendantRoutes;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->knowledgebaseRoute = m::mock(KnowledgebaseRoute::class);
        $this->topicRepository = m::mock(TopicRepository::class);

        $this->updateTopicDescendantRoutes = new UpdateTopicDescendantRoutes(
            $this->connection,
            $this->knowledgebaseRoute,
            $this->topicRepository
        );
    }

    /**
     * @test
     */
    public function update()
    {
        $topicId = 'topic-uuid';
        $knowledgebaseId = 'kb-uuid';
        $organizationId = 'org-uuid';
        $topic = TopicModel::createFromArray([
            'id' => $topicId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $topicRoute = KnowledgebaseRouteModel::createFromArray([
            'route' => 'updated-top-topic',
        ]);

        $topicAndDescendantsResult = [
            [
                'id' => $topicId,
                'url_id' => 'abc123',
                'url_slug' => 'updated-top-topic',
                'parent_topic_id' => null,
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'topic',
            ],
            [
                'id' => 'doc1-uuid',
                'url_id' => 'def456',
                'url_slug' => 'doc1',
                'parent_topic_id' => $topicId,
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'document',
            ],
            [
                'id' => 'sub-topic1-uuid',
                'url_id' => 'ghi789',
                'url_slug' => 'subtopic1',
                'parent_topic_id' => $topicId,
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'topic',
            ],
            [
                'id' => 'sub-topic2-uuid',
                'url_id' => 'jkl012',
                'url_slug' => 'subtopic2',
                'parent_topic_id' => $topicId,
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'topic',
            ],
            [
                'id' => 'sub-topic1-doc1-uuid',
                'url_id' => 'mno345',
                'url_slug' => 'doc1',
                'parent_topic_id' => 'sub-topic1-uuid',
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'document',
            ],
            [
                'id' => 'sub-topic1-doc2-uuid',
                'url_id' => 'pqr678',
                'url_slug' => 'doc2',
                'parent_topic_id' => 'sub-topic1-uuid',
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'document',
            ],
            [
                'id' => 'sub-topic2-doc1-uuid',
                'url_id' => 'stu901',
                'url_slug' => 'doc1',
                'parent_topic_id' => 'sub-topic2-uuid',
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'document',
            ],
            [
                'id' => 'sub-topic2-sub-topic1-uuid',
                'url_id' => 'vwx234',
                'url_slug' => 'subtopic1',
                'parent_topic_id' => 'sub-topic2-uuid',
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'topic',
            ],
            [
                'id' => 'sub-topic2-sub-topic1-doc1-uuid',
                'url_id' => 'yza567',
                'url_slug' => 'doc1',
                'parent_topic_id' => 'sub-topic2-sub-topic1-uuid',
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'document',
            ],
        ];

        $this->createTopicRepositoryExpectation(
            [$topicId, $knowledgebaseId, $organizationId],
            $topicAndDescendantsResult
        );

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(DocumentModel::class),
                'updated-top-topic/doc1',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-topic/doc1'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(TopicModel::class),
                'updated-top-topic/subtopic1',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-topic/subtopic1'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(TopicModel::class),
                'updated-top-topic/subtopic2',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-topic/subtopic2'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(DocumentModel::class),
                'updated-top-topic/subtopic1/doc1',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-topic/subtopic1/doc1'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(DocumentModel::class),
                'updated-top-topic/subtopic1/doc2',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-topic/subtopic1/doc2'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(DocumentModel::class),
                'updated-top-topic/subtopic2/doc1',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-topic/subtopic2/doc1'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(TopicModel::class),
                'updated-top-topic/subtopic2/subtopic1',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-topic/subtopic2/subtopic1'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(DocumentModel::class),
                'updated-top-topic/subtopic2/subtopic1/doc1',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-topic/subtopic2/subtopic1/doc1'])
        );
        $this->createConnectionCommitExpectation();

        $this->updateTopicDescendantRoutes->update($topic, $topicRoute);
    }

    private function createConnectionCommitExpectation()
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }

    private function createKnowledgebaseRouteExpectation($args, $result)
    {
        $this->knowledgebaseRoute
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createConnectionBeginTransactionExpectation()
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createTopicRepositoryExpectation($args, $result)
    {
        $this->topicRepository
            ->shouldReceive('getTopicAndDescendants')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
