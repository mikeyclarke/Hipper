<?php
declare(strict_types=1);

namespace Hipper\Tests\Topic;

use Doctrine\DBAL\Connection;
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
use Hipper\Topic\Storage\TopicUpdater as TopicStorageUpdater;
use Hipper\Topic\TopicUpdater;
use Hipper\Topic\TopicModel;
use Hipper\Topic\TopicRepository;
use Hipper\Topic\TopicValidator;
use Hipper\Topic\UpdateTopicDescendantRoutes;
use Hipper\Url\UrlSlugGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TopicUpdaterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $knowledgebaseOwner;
    private $knowledgebaseRepository;
    private $knowledgebaseRouteCreator;
    private $knowledgebaseRouteRepository;
    private $topicRepository;
    private $topicStorageUpdater;
    private $topicValidator;
    private $updateTopicDescendantRoutes;
    private $urlSlugGenerator;
    private $topicUpdater;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->knowledgebaseOwner = m::mock(KnowledgebaseOwner::class);
        $this->knowledgebaseRepository = m::mock(KnowledgebaseRepository::class);
        $this->knowledgebaseRouteCreator = m::mock(KnowledgebaseRouteCreator::class);
        $this->knowledgebaseRouteRepository = m::mock(KnowledgebaseRouteRepository::class);
        $this->topicRepository = m::mock(TopicRepository::class);
        $this->topicStorageUpdater = m::mock(TopicStorageUpdater::class);
        $this->topicValidator = m::mock(TopicValidator::class);
        $this->updateTopicDescendantRoutes = m::mock(UpdateTopicDescendantRoutes::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->topicUpdater = new TopicUpdater(
            $this->connection,
            $this->knowledgebaseOwner,
            $this->knowledgebaseRepository,
            $this->knowledgebaseRouteCreator,
            $this->knowledgebaseRouteRepository,
            $this->topicRepository,
            $this->topicStorageUpdater,
            $this->topicValidator,
            $this->updateTopicDescendantRoutes,
            $this->urlSlugGenerator
        );
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
        $this->createTopicStorageUpdaterExpectation(
            [$topicModel->getId(), ['name' => $name, 'url_slug' => $topicUrlSlug]],
            $topicUpdateResult
        );
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $parentTopicId],
            $parentTopicRouteResult
        );
        $this->createKnowledgebaseRouteCreatorExpectation(
            [$topicModel, $newRoute, true],
            $routeModel
        );
        $this->createConnectionCommitExpectation();
        $this->createUpdateTopicDescendantRoutesExpectation(
            [$topicModel, $routeModel]
        );

        $result = $this->topicUpdater->update($person, $topicModel, $parameters);
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
        $this->createTopicStorageUpdaterExpectation(
            [$topicModel->getId(), ['name' => $name]],
            $topicUpdateResult
        );
        $this->createConnectionCommitExpectation();

        $result = $this->topicUpdater->update($person, $topicModel, $parameters);
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
        $this->createTopicStorageUpdaterExpectation(
            [$topicModel->getId(), ['parent_topic_id' => $newParentTopicId]],
            $topicUpdateResult
        );
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $newParentTopicId],
            $parentTopicRouteResult
        );
        $this->createKnowledgebaseRouteCreatorExpectation(
            [$topicModel, $newRoute, true],
            $routeModel
        );
        $this->createConnectionCommitExpectation();
        $this->createUpdateTopicDescendantRoutesExpectation(
            [$topicModel, $routeModel]
        );

        $result = $this->topicUpdater->update($person, $topicModel, $parameters);
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
        $this->createTopicStorageUpdaterExpectation(
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
        $this->createKnowledgebaseRouteCreatorExpectation(
            [$topicModel, $newRoute, true],
            $routeModel
        );
        $this->createConnectionCommitExpectation();
        $this->createUpdateTopicDescendantRoutesExpectation(
            [$topicModel, $routeModel]
        );

        $result = $this->topicUpdater->update($person, $topicModel, $parameters);
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
        $this->createTopicStorageUpdaterExpectation(
            [$topicModel->getId(), ['description' => $description]],
            $topicUpdateResult
        );
        $this->createConnectionCommitExpectation();

        $result = $this->topicUpdater->update($person, $topicModel, $parameters);
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

        $result = $this->topicUpdater->update($person, $topicModel, $parameters);
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

        $this->topicUpdater->update($person, $topicModel, $parameters);
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

    private function createTopicStorageUpdaterExpectation($args, $result)
    {
        $this->topicStorageUpdater
            ->shouldReceive('update')
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

    private function createUrlSlugGeneratorExpectation($args, $result)
    {
        $this->urlSlugGenerator
            ->shouldReceive('generateFromString')
            ->once()
            ->with(...$args)
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
