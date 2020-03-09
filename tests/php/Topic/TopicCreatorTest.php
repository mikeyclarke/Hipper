<?php
declare(strict_types=1);

namespace Hipper\Tests\Topic;

use Doctrine\DBAL\Connection;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Knowledgebase\KnowledgebaseOwnerModelInterface;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseRouteCreator;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Organization\Exception\ResourceIsForeignToOrganizationException;
use Hipper\Person\PersonModel;
use Hipper\Project\ProjectModel;
use Hipper\Topic\Event\TopicCreatedEvent;
use Hipper\Topic\Storage\TopicInserter;
use Hipper\Topic\TopicCreator;
use Hipper\Topic\TopicModel;
use Hipper\Topic\TopicRepository;
use Hipper\Topic\TopicValidator;
use Hipper\Topic\UpdateTopicDescendantRoutes;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TopicCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $eventDispatcher;
    private $idGenerator;
    private $knowledgebaseOwner;
    private $knowledgebaseRepository;
    private $knowledgebaseRouteCreator;
    private $knowledgebaseRouteRepository;
    private $topicInserter;
    private $topicRepository;
    private $topicValidator;
    private $updateTopicDescendantRoutes;
    private $urlIdGenerator;
    private $urlSlugGenerator;
    private $topicCreator;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->eventDispatcher = m::mock(EventDispatcherInterface::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->knowledgebaseOwner = m::mock(KnowledgebaseOwner::class);
        $this->knowledgebaseRepository = m::mock(KnowledgebaseRepository::class);
        $this->knowledgebaseRouteCreator = m::mock(KnowledgebaseRouteCreator::class);
        $this->knowledgebaseRouteRepository = m::mock(KnowledgebaseRouteRepository::class);
        $this->topicInserter = m::mock(TopicInserter::class);
        $this->topicRepository = m::mock(TopicRepository::class);
        $this->topicValidator = m::mock(TopicValidator::class);
        $this->updateTopicDescendantRoutes = m::mock(UpdateTopicDescendantRoutes::class);
        $this->urlIdGenerator = m::mock(UrlIdGenerator::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->topicCreator = new TopicCreator(
            $this->connection,
            $this->eventDispatcher,
            $this->idGenerator,
            $this->knowledgebaseOwner,
            $this->knowledgebaseRepository,
            $this->knowledgebaseRouteCreator,
            $this->knowledgebaseRouteRepository,
            $this->topicInserter,
            $this->topicRepository,
            $this->topicValidator,
            $this->updateTopicDescendantRoutes,
            $this->urlIdGenerator,
            $this->urlSlugGenerator
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $person = new PersonModel;
        $person->setOrganizationId('org-uuid');
        $parameters = [
            'name' => 'My topic',
            'description' => 'My description',
            'knowledgebase_id' => 'kb-uuid',
        ];

        $knowledgebaseResult = [
            'id' => $parameters['knowledgebase_id'],
        ];
        $topicId = 'topic-uuid';
        $topicUrlSlug = 'my-topic';
        $topicUrlId = 'abcd1234';
        $topicInserterArgs = [
            $topicId,
            $parameters['name'],
            $topicUrlSlug,
            $topicUrlId,
            $parameters['knowledgebase_id'],
            $person->getOrganizationId(),
            $parameters['description'],
            null,
        ];
        $topicArray = [
            'id' => $topicId,
            'url_slug' => $topicUrlSlug,
            'url_id' => $topicUrlId,
        ];
        $knowledgebaseRouteModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new ProjectModel;

        $this->createKnowledgebaseRepositoryExpectation(
            [$parameters['knowledgebase_id'], 'org-uuid'],
            $knowledgebaseResult
        );
        $this->createTopicValidatorExpectation([$parameters, m::type(KnowledgebaseModel::class), null, true]);
        $this->createIdGeneratorExpectation($topicId);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $topicUrlSlug);
        $this->createUrlIdGeneratorExpectation($topicUrlId);
        $this->createConnectionBeginTransactionExpectation();
        $this->createTopicInserterExpectation($topicInserterArgs, $topicArray);
        $this->createKnowledgebaseRouteCreatorExpectation(
            [m::type(TopicModel::class), $topicUrlSlug, true, true],
            $knowledgebaseRouteModel
        );
        $this->createConnectionCommitExpectation();
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createEventDispatcherExpectation([m::type(TopicCreatedEvent::class), TopicCreatedEvent::NAME]);

        $result = $this->topicCreator->create($person, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(TopicModel::class, $result[0]);
        $this->assertEquals($topicId, $result[0]->getId());
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[1]);
        $this->assertInstanceOf(KnowledgebaseOwnerModelInterface::class, $result[2]);
    }

    /**
     * @test
     */
    public function createInParentTopic()
    {
        $person = new PersonModel;
        $person->setOrganizationId('org-uuid');
        $parameters = [
            'name' => 'My topic',
            'description' => 'My description',
            'knowledgebase_id' => 'kb-uuid',
            'parent_topic_id' => 'parent-topic-uuid',
        ];

        $knowledgebaseResult = [
            'id' => $parameters['knowledgebase_id'],
        ];
        $parentTopicResult = [
            'id' => $parameters['parent_topic_id'],
        ];
        $topicId = 'topic-uuid';
        $topicUrlSlug = 'my-topic';
        $topicUrlId = 'abcd1234';
        $topicInserterArgs = [
            $topicId,
            $parameters['name'],
            $topicUrlSlug,
            $topicUrlId,
            $parameters['knowledgebase_id'],
            $person->getOrganizationId(),
            $parameters['description'],
            $parameters['parent_topic_id'],
        ];
        $topicArray = [
            'id' => $topicId,
            'url_slug' => $topicUrlSlug,
            'url_id' => $topicUrlId,
        ];
        $parentTopicRouteResult = ['route' => 'i/have/nested'];
        $knowledgebaseRouteModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new ProjectModel;

        $this->createKnowledgebaseRepositoryExpectation(
            [$parameters['knowledgebase_id'], 'org-uuid'],
            $knowledgebaseResult
        );
        $this->createTopicRepositoryExpectation(
            [$parameters['parent_topic_id'], $parameters['knowledgebase_id'], 'org-uuid'],
            $parentTopicResult
        );
        $this->createTopicValidatorExpectation(
            [$parameters, m::type(KnowledgebaseModel::class), m::type(TopicModel::class), true]
        );
        $this->createIdGeneratorExpectation($topicId);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $topicUrlSlug);
        $this->createUrlIdGeneratorExpectation($topicUrlId);
        $this->createConnectionBeginTransactionExpectation();
        $this->createTopicInserterExpectation($topicInserterArgs, $topicArray);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            ['org-uuid', $parameters['knowledgebase_id'], $parameters['parent_topic_id']],
            $parentTopicRouteResult
        );
        $this->createKnowledgebaseRouteCreatorExpectation(
            [m::type(TopicModel::class), $parentTopicRouteResult['route'] . '/' . $topicUrlSlug, true, true],
            $knowledgebaseRouteModel
        );
        $this->createConnectionCommitExpectation();
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createEventDispatcherExpectation([m::type(TopicCreatedEvent::class), TopicCreatedEvent::NAME]);

        $result = $this->topicCreator->create($person, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(TopicModel::class, $result[0]);
        $this->assertEquals($topicId, $result[0]->getId());
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[1]);
        $this->assertInstanceOf(KnowledgebaseOwnerModelInterface::class, $result[2]);
    }

    private function createEventDispatcherExpectation($args)
    {
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(...$args);
    }

    private function createUpdateTopicDescendantRoutesExpectation($args)
    {
        $this->updateTopicDescendantRoutes
            ->shouldReceive('update')
            ->once()
            ->with(...$args);
    }

    private function createKnowledgebaseOwnerExpectation($args, $result)
    {
        $this->knowledgebaseOwner
            ->shouldReceive('get')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createConnectionCommitExpectation()
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }

    private function createKnowledgebaseRouteCreatorExpectation($args, $result)
    {
        $this->knowledgebaseRouteCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseRouteRepositoryExpectation($args, $result)
    {
        $this->knowledgebaseRouteRepository
            ->shouldReceive('findCanonicalRouteForTopic')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createTopicInserterExpectation($args, $result)
    {
        $this->topicInserter
            ->shouldReceive('insert')
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

    private function createUrlIdGeneratorExpectation($result)
    {
        $this->urlIdGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }

    private function createUrlSlugGeneratorExpectation($args, $result)
    {
        $this->urlSlugGenerator
            ->shouldReceive('generateFromString')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createIdGeneratorExpectation($result)
    {
        $this->idGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }

    private function createTopicRepositoryExpectation($args, $result)
    {
        $this->topicRepository
            ->shouldReceive('findByIdInKnowledgebase')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseRepositoryExpectation($args, $result)
    {
        $this->knowledgebaseRepository
            ->shouldReceive('findById')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createTopicValidatorExpectation($args)
    {
        $this->topicValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }
}
