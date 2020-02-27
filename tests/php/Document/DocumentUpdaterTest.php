<?php
declare(strict_types=1);

namespace Hipper\Tests\Document;

use Doctrine\DBAL\Connection;
use Hipper\Document\DocumentDescriptionDeducer;
use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRenderer;
use Hipper\Document\DocumentRevisionCreator;
use Hipper\Document\DocumentUpdater;
use Hipper\Document\DocumentValidator;
use Hipper\Document\Renderer\RendererResult;
use Hipper\Document\Storage\DocumentUpdater as DocumentStorageUpdater;
use Hipper\Document\Exception\KnowledgebaseNotFoundException;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Person\PersonModel;
use Hipper\Team\TeamModel;
use Hipper\Topic\TopicModel;
use Hipper\Topic\TopicRepository;
use Hipper\Url\UrlSlugGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DocumentUpdaterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $documentDescriptionDeducer;
    private $documentRenderer;
    private $documentRevisionCreator;
    private $documentStorageUpdater;
    private $documentValidator;
    private $knowledgebaseOwner;
    private $knowledgebaseRepository;
    private $knowledgebaseRoute;
    private $knowledgebaseRouteRepository;
    private $topicRepository;
    private $urlSlugGenerator;
    private $documentUpdater;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->documentDescriptionDeducer = m::mock(DocumentDescriptionDeducer::class);
        $this->documentRenderer = m::mock(DocumentRenderer::class);
        $this->documentRevisionCreator = m::mock(DocumentRevisionCreator::class);
        $this->documentStorageUpdater = m::mock(DocumentStorageUpdater::class);
        $this->documentValidator = m::mock(DocumentValidator::class);
        $this->knowledgebaseOwner = m::mock(KnowledgebaseOwner::class);
        $this->knowledgebaseRepository = m::mock(KnowledgebaseRepository::class);
        $this->knowledgebaseRoute = m::mock(KnowledgebaseRoute::class);
        $this->knowledgebaseRouteRepository = m::mock(KnowledgebaseRouteRepository::class);
        $this->topicRepository = m::mock(TopicRepository::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->documentUpdater = new DocumentUpdater(
            $this->connection,
            $this->documentDescriptionDeducer,
            $this->documentRenderer,
            $this->documentRevisionCreator,
            $this->documentStorageUpdater,
            $this->documentValidator,
            $this->knowledgebaseOwner,
            $this->knowledgebaseRepository,
            $this->knowledgebaseRoute,
            $this->knowledgebaseRouteRepository,
            $this->topicRepository,
            $this->urlSlugGenerator
        );
    }

    /**
     * @test
     */
    public function updateName()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $documentId = 'doc-uuid';
        $existingTopicId = 'topic-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $document = new DocumentModel;
        $document->setId($documentId);
        $document->setName('My doc');
        $document->setKnowledgebaseId($knowledgebaseId);
        $document->setTopicId($existingTopicId);
        $document->setUrlSlug('my-doc');

        $parameters = [
            'name' => 'My updated doc',
        ];

        $kbResult = ['id' => $knowledgebaseId];
        $topicResult = ['id' => $existingTopicId];
        $urlSlug = 'my-updated-doc';
        $propertiesToUpdate = [
            'last_updated_by' => $personId,
            'name' => $parameters['name'],
            'url_slug' => $urlSlug,
        ];
        $docUpdateResult = [
            'name' => $parameters['name'],
            'url_slug' => $urlSlug,
        ];
        $topicRoute = 'my/nested/topic';
        $topicRouteResult = ['route' => $topicRoute];
        $docRoute = $topicRoute . '/' . $urlSlug;
        $routeModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $kbResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createDocumentValidatorExpectation([$parameters, null, null]);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $urlSlug);
        $this->createTopicRepositoryExpectation(
            [$existingTopicId, $knowledgebaseId, $organizationId],
            $topicResult
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createDocumentStorageUpdaterExpectation([$document->getId(), $propertiesToUpdate], $docUpdateResult);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $existingTopicId],
            $topicRouteResult
        );
        $this->createKnowledgebaseRouteExpectation([$document, $docRoute, true], $routeModel);
        $this->createDocumentRevisionCreatorExpectation([$document]);
        $this->createConnectionCommitExpectation();

        $result = $this->documentUpdater->update($person, $document, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
        $this->assertEquals($parameters['name'], $document->getName());
        $this->assertEquals($urlSlug, $document->getUrlSlug());
    }

    /**
     * @test
     */
    public function newRouteIsNotGeneratedIfUpdatedNameResultsInIdenticalUrlSlug()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $documentId = 'doc-uuid';
        $existingTopicId = 'prev-topic-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $document = new DocumentModel;
        $document->setId($documentId);
        $document->setName('My doc');
        $document->setKnowledgebaseId($knowledgebaseId);
        $document->setTopicId($existingTopicId);
        $document->setUrlSlug('my-doc');

        $parameters = [
            'name' => 'My Doc',
        ];

        $kbResult = ['id' => $knowledgebaseId];
        $topicResult = ['id' => $existingTopicId];
        $urlSlug = 'my-doc';
        $propertiesToUpdate = [
            'last_updated_by' => $personId,
            'name' => $parameters['name'],
        ];
        $docUpdateResult = [
            'name' => $parameters['name'],
        ];
        $docRouteResult = ['route'];
        $routeModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $kbResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createDocumentValidatorExpectation([$parameters, null, null]);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $urlSlug);
        $this->createKnowledgebaseRouteRepositoryDocumentExpectation(
            [$organizationId, $knowledgebaseId, $documentId],
            $docRouteResult
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createDocumentStorageUpdaterExpectation([$document->getId(), $propertiesToUpdate], $docUpdateResult);
        $this->createDocumentRevisionCreatorExpectation([$document]);
        $this->createConnectionCommitExpectation();

        $result = $this->documentUpdater->update($person, $document, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
        $this->assertEquals($parameters['name'], $document->getName());
        $this->assertEquals($urlSlug, $document->getUrlSlug());
    }

    /**
     * @test
     */
    public function moveToNewTopic()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $documentId = 'doc-uuid';
        $previousTopicId = 'prev-topic-uuid';
        $topicId = 'topic-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $document = new DocumentModel;
        $document->setId($documentId);
        $document->setKnowledgebaseId($knowledgebaseId);
        $document->setTopicId($previousTopicId);
        $document->setUrlSlug('my-doc');

        $parameters = [
            'topic_id' => $topicId,
        ];

        $kbResult = ['id' => $knowledgebaseId];
        $topicResult = ['id' => $topicId];
        $propertiesToUpdate = [
            'last_updated_by' => $personId,
            'topic_id' => $topicId,
        ];
        $docUpdateResult = [
            'topic_id' => $topicId,
        ];
        $topicRoute = 'my/nested/topic';
        $topicRouteResult = ['route' => $topicRoute];
        $docRoute = $topicRoute . '/' . $document->getUrlSlug();
        $routeModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $kbResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createTopicRepositoryExpectation([$topicId, $knowledgebaseId, $organizationId], $topicResult);
        $this->createDocumentValidatorExpectation(
            [$parameters, null, m::type(TopicModel::class)]
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createDocumentStorageUpdaterExpectation([$document->getId(), $propertiesToUpdate], $docUpdateResult);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $topicId],
            $topicRouteResult
        );
        $this->createKnowledgebaseRouteExpectation([$document, $docRoute, true], $routeModel);
        $this->createDocumentRevisionCreatorExpectation([$document]);
        $this->createConnectionCommitExpectation();

        $result = $this->documentUpdater->update($person, $document, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
        $this->assertEquals($topicId, $document->getTopicId());
    }

    /**
     * @test
     */
    public function updateNameWhilstMovingToNewTopic()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $documentId = 'doc-uuid';
        $previousTopicId = 'prev-topic-uuid';
        $topicId = 'topic-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $document = new DocumentModel;
        $document->setId($documentId);
        $document->setName('My doc');
        $document->setKnowledgebaseId($knowledgebaseId);
        $document->setTopicId($previousTopicId);
        $document->setUrlSlug('my-doc');

        $parameters = [
            'name' => 'My updated doc',
            'topic_id' => $topicId,
        ];

        $kbResult = ['id' => $knowledgebaseId];
        $topicResult = ['id' => $topicId];
        $urlSlug = 'my-updated-doc';
        $propertiesToUpdate = [
            'last_updated_by' => $personId,
            'name' => $parameters['name'],
            'topic_id' => $topicId,
            'url_slug' => $urlSlug,
        ];
        $docUpdateResult = [
            'name' => $parameters['name'],
            'topic_id' => $topicId,
            'url_slug' => $urlSlug,
        ];
        $topicRoute = 'my/nested/topic';
        $topicRouteResult = ['route' => $topicRoute];
        $docRoute = $topicRoute . '/' . $urlSlug;
        $routeModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $kbResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createTopicRepositoryExpectation([$topicId, $knowledgebaseId, $organizationId], $topicResult);
        $this->createDocumentValidatorExpectation(
            [$parameters, null, m::type(TopicModel::class)]
        );
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $urlSlug);
        $this->createConnectionBeginTransactionExpectation();
        $this->createDocumentStorageUpdaterExpectation([$document->getId(), $propertiesToUpdate], $docUpdateResult);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $topicId],
            $topicRouteResult
        );
        $this->createKnowledgebaseRouteExpectation([$document, $docRoute, true], $routeModel);
        $this->createDocumentRevisionCreatorExpectation([$document]);
        $this->createConnectionCommitExpectation();

        $result = $this->documentUpdater->update($person, $document, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
        $this->assertEquals($topicId, $document->getTopicId());
        $this->assertEquals($parameters['name'], $document->getName());
        $this->assertEquals($urlSlug, $document->getUrlSlug());
    }

    /**
     * @test
     */
    public function updateContent()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $documentId = 'doc-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $document = new DocumentModel;
        $document->setId($documentId);
        $document->setName('Foo');
        $document->setContent('{"type": "text", "text": "👋 Congrats on joining Hipper!"}');
        $document->setDeducedDescription('👋 Congrats on joining Hipper!');
        $document->setKnowledgebaseId($knowledgebaseId);

        $parameters = [
            'content' => [
                "type" => "text",
                "text" => "An introduction to our stack and the systems that we’re responsible for."
            ],
        ];

        $contentEncoded = json_encode($parameters['content']);
        $kbResult = ['id' => $knowledgebaseId];
        $deducedDescription = 'An introduction to our stack and the systems that we’re responsible for.';
        $contentPlain = 'An introduction to our stack and the systems that we’re responsible for.';
        $rendererResult = new RendererResult;
        $rendererResult->setContent($contentPlain);
        $propertiesToUpdate = [
            'last_updated_by' => $personId,
            'content' => $contentEncoded,
            'content_plain' => $contentPlain,
            'deduced_description' => $deducedDescription,
        ];
        $docUpdateResult = [
            'content' => $contentEncoded,
            'deduced_description' => $deducedDescription,
        ];
        $docRouteResult = ['route'];
        $routeModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $kbResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createDocumentValidatorExpectation([$parameters, null, null]);
        $this->createDocumentDescriptionDeducerExpectation([$parameters['content']], $deducedDescription);
        $this->createDocumentRendererExpectation([json_encode($parameters['content']), 'text'], $rendererResult);
        $this->createKnowledgebaseRouteRepositoryDocumentExpectation(
            [$organizationId, $knowledgebaseId, $documentId],
            $docRouteResult
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createDocumentStorageUpdaterExpectation([$document->getId(), $propertiesToUpdate], $docUpdateResult);
        $this->createDocumentRevisionCreatorExpectation([$document]);
        $this->createConnectionCommitExpectation();

        $result = $this->documentUpdater->update($person, $document, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
        $this->assertEquals($contentEncoded, $document->getContent());
        $this->assertEquals($deducedDescription, $document->getDeducedDescription());
    }

    /**
     * @test
     */
    public function updateDescription()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $documentId = 'doc-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $document = new DocumentModel;
        $document->setId($documentId);
        $document->setName('Foo');
        $document->setDescription('Baz');
        $document->setKnowledgebaseId($knowledgebaseId);

        $parameters = [
            'description' => 'Foo bar',
        ];

        $kbResult = ['id' => $knowledgebaseId];
        $deducedDescription = 'An introduction to our stack and the systems that we’re responsible for.';
        $propertiesToUpdate = [
            'last_updated_by' => $personId,
            'description' => $parameters['description'],
        ];
        $docUpdateResult = [
            'description' => $parameters['description'],
        ];
        $docRouteResult = ['route'];
        $routeModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $kbResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createDocumentValidatorExpectation([$parameters, null, null]);
        $this->createKnowledgebaseRouteRepositoryDocumentExpectation(
            [$organizationId, $knowledgebaseId, $documentId],
            $docRouteResult
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createDocumentStorageUpdaterExpectation([$document->getId(), $propertiesToUpdate], $docUpdateResult);
        $this->createDocumentRevisionCreatorExpectation([$document]);
        $this->createConnectionCommitExpectation();

        $result = $this->documentUpdater->update($person, $document, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
        $this->assertEquals($parameters['description'], $document->getDescription());
    }

    /**
     * @test
     */
    public function updateWithNothingNew()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $documentId = 'doc-uuid';

        $deducedDescription = '👋 Congrats on joining Hipper!';
        $content = [
            "type" => "text",
            "text" => "👋 Congrats on joining Hipper!"
        ];

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $document = new DocumentModel;
        $document->setId($documentId);
        $document->setName('Foo');
        $document->setContent(json_encode($content));
        $document->setDeducedDescription($deducedDescription);
        $document->setKnowledgebaseId($knowledgebaseId);

        $parameters = [
            'name' => 'Foo',
            'content' => $content,
        ];

        $kbResult = ['id' => $knowledgebaseId];
        $docRouteResult = ['route'];
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $kbResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createDocumentValidatorExpectation([$parameters, null, null]);
        $this->createKnowledgebaseRouteRepositoryDocumentExpectation(
            [$organizationId, $knowledgebaseId, $documentId],
            $docRouteResult
        );

        $result = $this->documentUpdater->update($person, $document, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
    }

    /**
     * @test
     */
    public function documentKnowledgebaseNotFound()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $documentId = 'doc-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $document = new DocumentModel;
        $document->setId($documentId);
        $document->setKnowledgebaseId($knowledgebaseId);

        $parameters = [
            'name' => 'foo',
        ];

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], null);

        $this->expectException(KnowledgebaseNotFoundException::class);
        $this->documentUpdater->update($person, $document, $parameters);
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

    private function createDocumentRevisionCreatorExpectation($args)
    {
        $this->documentRevisionCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args);
    }

    private function createKnowledgebaseRouteExpectation($args, $result)
    {
        $this->knowledgebaseRoute
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseRouteRepositoryDocumentExpectation($args, $result)
    {
        $this->knowledgebaseRouteRepository
            ->shouldReceive('findCanonicalRouteForDocument')
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

    private function createDocumentStorageUpdaterExpectation($args, $result)
    {
        $this->documentStorageUpdater
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

    private function createDocumentRendererExpectation($args, $result)
    {
        $this->documentRenderer
            ->shouldReceive('render')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createDocumentDescriptionDeducerExpectation($args, $result)
    {
        $this->documentDescriptionDeducer
            ->shouldReceive('deduce')
            ->once()
            ->with(...$args)
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

    private function createDocumentValidatorExpectation($args)
    {
        $this->documentValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
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
}
