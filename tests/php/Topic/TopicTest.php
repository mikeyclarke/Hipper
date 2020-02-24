<?php
declare(strict_types=1);

namespace Hipper\Tests\Topic;

use Doctrine\DBAL\Connection;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Knowledgebase\KnowledgebaseOwnerModelInterface;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Organization\Exception\ResourceIsForeignToOrganizationException;
use Hipper\Person\PersonModel;
use Hipper\Project\ProjectModel;
use Hipper\Topic\Topic;
use Hipper\Topic\TopicInserter;
use Hipper\Topic\TopicModel;
use Hipper\Topic\TopicRepository;
use Hipper\Topic\TopicUpdater;
use Hipper\Topic\TopicValidator;
use Hipper\Topic\UpdateTopicDescendantRoutes;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TopicTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $idGenerator;
    private $knowledgebaseOwner;
    private $knowledgebaseRepository;
    private $knowledgebaseRoute;
    private $knowledgebaseRouteRepository;
    private $topicInserter;
    private $topicRepository;
    private $topicUpdater;
    private $topicValidator;
    private $updateTopicDescendantRoutes;
    private $urlIdGenerator;
    private $urlSlugGenerator;
    private $topic;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->knowledgebaseOwner = m::mock(KnowledgebaseOwner::class);
        $this->knowledgebaseRepository = m::mock(KnowledgebaseRepository::class);
        $this->knowledgebaseRoute = m::mock(KnowledgebaseRoute::class);
        $this->knowledgebaseRouteRepository = m::mock(KnowledgebaseRouteRepository::class);
        $this->topicInserter = m::mock(TopicInserter::class);
        $this->topicRepository = m::mock(TopicRepository::class);
        $this->topicUpdater = m::mock(TopicUpdater::class);
        $this->topicValidator = m::mock(TopicValidator::class);
        $this->updateTopicDescendantRoutes = m::mock(UpdateTopicDescendantRoutes::class);
        $this->urlIdGenerator = m::mock(UrlIdGenerator::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->topic = new Topic(
            $this->connection,
            $this->idGenerator,
            $this->knowledgebaseOwner,
            $this->knowledgebaseRepository,
            $this->knowledgebaseRoute,
            $this->knowledgebaseRouteRepository,
            $this->topicInserter,
            $this->topicRepository,
            $this->topicUpdater,
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
        $this->createKnowledgebaseRouteExpectation(
            [m::type(TopicModel::class), $topicUrlSlug, true, true],
            $knowledgebaseRouteModel
        );
        $this->createConnectionCommitExpectation();
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);

        $result = $this->topic->create($person, $parameters);
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
        $this->createKnowledgebaseRouteExpectation(
            [m::type(TopicModel::class), $parentTopicRouteResult['route'] . '/' . $topicUrlSlug, true, true],
            $knowledgebaseRouteModel
        );
        $this->createConnectionCommitExpectation();
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);

        $result = $this->topic->create($person, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(TopicModel::class, $result[0]);
        $this->assertEquals($topicId, $result[0]->getId());
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[1]);
        $this->assertInstanceOf(KnowledgebaseOwnerModelInterface::class, $result[2]);
    }

    /**
     * @test
     */
    public function updateName()
    {
        $topicId = 'topic-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $parentTopicId = 'parent-topic-uuid';
        $name = 'Bar';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $topicModel = TopicModel::createFromArray([
            'id' => $topicId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'url_slug' => 'foo',
            'parent_topic_id' => $parentTopicId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $parameters = [
            'name' => $name,
        ];

        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $knowledgebaseOwnerModel = new ProjectModel;
        $topicUrlSlug = 'bar';
        $parentTopicResult = [
            'id' => $parentTopicId,
        ];
        $topicUpdateResult = [
            'name' => $name,
            'url_slug' => $topicUrlSlug,
        ];
        $parentTopicRouteResult = ['route' => 'parent-topic'];
        $newRoute = sprintf('%s/%s', $parentTopicRouteResult['route'], $topicUrlSlug);
        $routeModel = KnowledgebaseRouteModel::createFromArray([
            'route' => $newRoute,
        ]);

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $knowledgebaseResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createTopicValidatorExpectation([$parameters, null, null]);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $topicUrlSlug);
        $this->createTopicRepositoryExpectation(
            [$parentTopicId, $knowledgebaseId, $organizationId],
            $parentTopicResult
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createTopicUpdaterExpectation(
            [$topicModel->getId(), ['name' => $name, 'url_slug' => $topicUrlSlug]],
            $topicUpdateResult
        );
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $parentTopicId],
            $parentTopicRouteResult
        );
        $this->createKnowledgebaseRouteExpectation(
            [$topicModel, $newRoute, true],
            $routeModel
        );
        $this->createConnectionCommitExpectation();
        $this->createUpdateTopicDescendantRoutesExpectation(
            [$topicModel, $routeModel]
        );

        $result = $this->topic->update($person, $topicModel, $parameters);
        $this->assertEquals($name, $topicModel->getName());
        $this->assertEquals($topicUrlSlug, $topicModel->getUrlSlug());
        $this->assertIsArray($result);
        $this->assertEquals($topicModel, $result[0]);
        $this->assertEquals($routeModel, $result[1]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[2]);
    }

    /**
     * @test
     */
    public function newRouteIsNotGeneratedIfUpdatedNameResultsInIdenticalUrlSlug()
    {
        $topicId = 'topic-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $parentTopicId = 'parent-topic-uuid';
        $name = 'FOO';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $topicModel = TopicModel::createFromArray([
            'id' => $topicId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'url_slug' => 'foo',
            'parent_topic_id' => $parentTopicId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $parameters = [
            'name' => $name,
        ];

        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $knowledgebaseOwnerModel = new ProjectModel;
        $topicUrlSlug = 'foo';
        $topicRouteResult = ['route' => 'parent-topic/foo'];
        $topicUpdateResult = [
            'name' => $name,
        ];

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $knowledgebaseResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createTopicValidatorExpectation([$parameters, null, null]);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $topicUrlSlug);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $topicId],
            $topicRouteResult
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createTopicUpdaterExpectation(
            [$topicModel->getId(), ['name' => $name]],
            $topicUpdateResult
        );
        $this->createConnectionCommitExpectation();

        $result = $this->topic->update($person, $topicModel, $parameters);
        $this->assertEquals($name, $topicModel->getName());
        $this->assertEquals($topicUrlSlug, $topicModel->getUrlSlug());
        $this->assertIsArray($result);
        $this->assertEquals($topicModel, $result[0]);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[1]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[2]);
    }

    /**
     * @test
     */
    public function moveToNewParentTopic()
    {
        $topicId = 'topic-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $parentTopicId = 'parent-topic-uuid';
        $newParentTopicId = 'new-parent-uuid';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $topicModel = TopicModel::createFromArray([
            'id' => $topicId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'url_slug' => 'foo',
            'parent_topic_id' => $parentTopicId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $parameters = [
            'parent_topic_id' => $newParentTopicId,
        ];

        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $knowledgebaseOwnerModel = new ProjectModel;
        $parentTopicResult = [
            'id' => $newParentTopicId,
        ];
        $topicUpdateResult = [
            'parent_topic_id' => $newParentTopicId,
        ];
        $parentTopicRouteResult = ['route' => 'new-parent-topic'];
        $newRoute = sprintf('%s/%s', $parentTopicRouteResult['route'], $topicModel->getUrlSlug());
        $routeModel = KnowledgebaseRouteModel::createFromArray([
            'route' => $newRoute,
        ]);

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $knowledgebaseResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createTopicRepositoryExpectation(
            [$newParentTopicId, $knowledgebaseId, $organizationId],
            $parentTopicResult
        );
        $this->createTopicValidatorExpectation([$parameters, null, m::type(TopicModel::class)]);
        $this->createConnectionBeginTransactionExpectation();
        $this->createTopicUpdaterExpectation(
            [$topicModel->getId(), ['parent_topic_id' => $newParentTopicId]],
            $topicUpdateResult
        );
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $newParentTopicId],
            $parentTopicRouteResult
        );
        $this->createKnowledgebaseRouteExpectation(
            [$topicModel, $newRoute, true],
            $routeModel
        );
        $this->createConnectionCommitExpectation();
        $this->createUpdateTopicDescendantRoutesExpectation(
            [$topicModel, $routeModel]
        );

        $result = $this->topic->update($person, $topicModel, $parameters);
        $this->assertEquals($newParentTopicId, $topicModel->getParentTopicId());
        $this->assertIsArray($result);
        $this->assertEquals($topicModel, $result[0]);
        $this->assertEquals($routeModel, $result[1]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[2]);
    }

    /**
     * @test
     */
    public function updateNameWhilstMovingToNewParentTopic()
    {
        $topicId = 'topic-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $parentTopicId = 'parent-topic-uuid';
        $name = 'Bar';
        $newParentTopicId = 'new-parent-uuid';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $topicModel = TopicModel::createFromArray([
            'id' => $topicId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'url_slug' => 'foo',
            'parent_topic_id' => $parentTopicId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $parameters = [
            'name' => $name,
            'parent_topic_id' => $newParentTopicId,
        ];

        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $knowledgebaseOwnerModel = new ProjectModel;
        $parentTopicResult = [
            'id' => $newParentTopicId,
        ];
        $topicUrlSlug = 'bar';
        $topicUpdateResult = [
            'name' => $name,
            'parent_topic_id' => $newParentTopicId,
            'url_slug' => $topicUrlSlug,
        ];
        $parentTopicRouteResult = ['route' => 'new-parent-topic'];
        $newRoute = sprintf('%s/%s', $parentTopicRouteResult['route'], $topicUrlSlug);
        $routeModel = KnowledgebaseRouteModel::createFromArray([
            'route' => $newRoute,
        ]);

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $knowledgebaseResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createTopicRepositoryExpectation(
            [$newParentTopicId, $knowledgebaseId, $organizationId],
            $parentTopicResult
        );
        $this->createTopicValidatorExpectation([$parameters, null, m::type(TopicModel::class)]);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $topicUrlSlug);
        $this->createConnectionBeginTransactionExpectation();
        $this->createTopicUpdaterExpectation(
            [
                $topicModel->getId(),
                ['name' => $name, 'url_slug' => $topicUrlSlug, 'parent_topic_id' => $newParentTopicId]
            ],
            $topicUpdateResult
        );
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $newParentTopicId],
            $parentTopicRouteResult
        );
        $this->createKnowledgebaseRouteExpectation(
            [$topicModel, $newRoute, true],
            $routeModel
        );
        $this->createConnectionCommitExpectation();
        $this->createUpdateTopicDescendantRoutesExpectation(
            [$topicModel, $routeModel]
        );

        $result = $this->topic->update($person, $topicModel, $parameters);
        $this->assertEquals($newParentTopicId, $topicModel->getParentTopicId());
        $this->assertEquals($name, $topicModel->getName());
        $this->assertEquals($topicUrlSlug, $topicModel->getUrlSlug());
        $this->assertIsArray($result);
        $this->assertEquals($topicModel, $result[0]);
        $this->assertEquals($routeModel, $result[1]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[2]);
    }

    /**
     * @test
     */
    public function updateDescription()
    {
        $topicId = 'topic-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $description = 'I changed my description';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $topicModel = TopicModel::createFromArray([
            'id' => $topicId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $parameters = [
            'description' => $description,
        ];

        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $knowledgebaseOwnerModel = new ProjectModel;
        $topicRouteResult = ['route' => 'foo'];
        $topicUpdateResult = [
            'description' => $description,
        ];

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $knowledgebaseResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createTopicValidatorExpectation([$parameters, null, null]);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $topicId],
            $topicRouteResult
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createTopicUpdaterExpectation(
            [$topicModel->getId(), ['description' => $description]],
            $topicUpdateResult
        );
        $this->createConnectionCommitExpectation();

        $result = $this->topic->update($person, $topicModel, $parameters);
        $this->assertEquals($description, $topicModel->getDescription());
        $this->assertIsArray($result);
        $this->assertEquals($topicModel, $result[0]);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[1]);
        $this->assertInstanceOf(KnowledgebaseOwnerModelInterface::class, $result[2]);
    }

    /**
     * @test
     */
    public function updateWithNothingNew()
    {
        $topicId = 'topic-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $topicModel = TopicModel::createFromArray([
            'id' => $topicId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $parameters = [
            'name' => 'Foo',
            'description' => 'This is my description',
        ];

        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $knowledgebaseOwnerModel = new ProjectModel;
        $topicRouteResult = ['route' => 'foo'];

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $knowledgebaseResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createTopicValidatorExpectation([$parameters, null, null]);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $topicId],
            $topicRouteResult
        );

        $result = $this->topic->update($person, $topicModel, $parameters);
        $this->assertIsArray($result);
        $this->assertEquals($topicModel, $result[0]);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[1]);
        $this->assertInstanceOf(KnowledgebaseOwnerModelInterface::class, $result[2]);
    }

    /**
     * @test
     */
    public function updateTopicOutsideCurrentUsersOrganization()
    {
        $topicId = 'topic-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $foreignOrganizationId = 'foreign-org-uuid';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $topicModel = TopicModel::createFromArray([
            'id' => $topicId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $foreignOrganizationId,
        ]);
        $parameters = [
            'name' => 'Foo',
            'description' => 'This is my description',
        ];

        $this->expectException(ResourceIsForeignToOrganizationException::class);

        $this->topic->update($person, $topicModel, $parameters);
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

    private function createKnowledgebaseRouteExpectation($args, $result)
    {
        $this->knowledgebaseRoute
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

    private function createTopicUpdaterExpectation($args, $result)
    {
        $this->topicUpdater
            ->shouldReceive('update')
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
