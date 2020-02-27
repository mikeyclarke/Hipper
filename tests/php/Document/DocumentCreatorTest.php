<?php
declare(strict_types=1);

namespace Hipper\Tests\Document;

use Doctrine\DBAL\Connection;
use Hipper\Document\DocumentCreator;
use Hipper\Document\DocumentDescriptionDeducer;
use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRenderer;
use Hipper\Document\DocumentRevisionCreator;
use Hipper\Document\DocumentValidator;
use Hipper\Document\Renderer\RendererResult;
use Hipper\Document\Storage\DocumentInserter;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseRouteCreator;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Person\PersonModel;
use Hipper\Team\TeamModel;
use Hipper\Topic\TopicModel;
use Hipper\Topic\TopicRepository;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DocumentCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $documentDescriptionDeducer;
    private $documentInserter;
    private $documentRenderer;
    private $documentRevisionCreator;
    private $documentValidator;
    private $idGenerator;
    private $knowledgebaseOwner;
    private $knowledgebaseRepository;
    private $knowledgebaseRouteCreator;
    private $knowledgebaseRouteRepository;
    private $topicRepository;
    private $urlIdGenerator;
    private $urlSlugGenerator;
    private $documentCreator;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->documentDescriptionDeducer = m::mock(DocumentDescriptionDeducer::class);
        $this->documentInserter = m::mock(DocumentInserter::class);
        $this->documentRenderer = m::mock(DocumentRenderer::class);
        $this->documentRevisionCreator = m::mock(DocumentRevisionCreator::class);
        $this->documentValidator = m::mock(DocumentValidator::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->knowledgebaseOwner = m::mock(KnowledgebaseOwner::class);
        $this->knowledgebaseRepository = m::mock(KnowledgebaseRepository::class);
        $this->knowledgebaseRouteCreator = m::mock(KnowledgebaseRouteCreator::class);
        $this->knowledgebaseRouteRepository = m::mock(KnowledgebaseRouteRepository::class);
        $this->topicRepository = m::mock(TopicRepository::class);
        $this->urlIdGenerator = m::mock(UrlIdGenerator::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->documentCreator = new DocumentCreator(
            $this->connection,
            $this->documentDescriptionDeducer,
            $this->documentInserter,
            $this->documentRenderer,
            $this->documentRevisionCreator,
            $this->documentValidator,
            $this->idGenerator,
            $this->knowledgebaseOwner,
            $this->knowledgebaseRepository,
            $this->knowledgebaseRouteCreator,
            $this->knowledgebaseRouteRepository,
            $this->topicRepository,
            $this->urlIdGenerator,
            $this->urlSlugGenerator
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $parameters = [
            'name' => 'Welcome to Engineering',
            'description' => null,
            'content' => ["type" => "text", "text" => "👋 Congrats on joining Hipper!"],
            'knowledgebase_id' => $knowledgebaseId,
        ];

        $kbResult = ['knowledgebase'];
        $documentId = 'doc-uuid';
        $urlSlug = 'welcome-to-engineering';
        $urlId = 'url-id';
        $deducedDescription = '👋 Congrats on joining Hipper!';
        $contentPlain = '👋 Congrats on joining Hipper!';
        $rendererResult = new RendererResult;
        $rendererResult->setContent($contentPlain);

        $documentRow = [
            'url_slug' => $urlSlug,
            'url_id' => $urlId,
        ];
        $routeModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$parameters['knowledgebase_id'], $organizationId], $kbResult);
        $this->createDocumentValidatorExpectation([$parameters, m::type(KnowledgebaseModel::class), null, true]);
        $this->createIdGeneratorExpectation($documentId);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $urlSlug);
        $this->createUrlIdGeneratorExpectation($urlId);
        $this->createDocumentDescriptionDeducerExpectation([$parameters['content']], $deducedDescription);
        $this->createDocumentRendererExpectation([json_encode($parameters['content']), 'text'], $rendererResult);
        $this->createConnectionBeginTransactionExpectation();
        $this->createDocumentInserterExpectation(
            [
                $documentId,
                $parameters['name'],
                $urlSlug,
                $urlId,
                $knowledgebaseId,
                $organizationId,
                $personId,
                $parameters['description'],
                $deducedDescription,
                json_encode($parameters['content']),
                $contentPlain,
                null
            ],
            $documentRow
        );
        $this->createKnowledgebaseRouteCreatorExpectation(
            [m::type(DocumentModel::class), $urlSlug, true, true],
            $routeModel
        );
        $this->createDocumentRevisionCreatorExpectation([m::type(DocumentModel::class)]);
        $this->createConnectionCommitExpectation();
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);

        $result = $this->documentCreator->create($person, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
    }

    /**
     * @test
     */
    public function createInTopic()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $topicId = 'topic-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $parameters = [
            'name' => 'Welcome to Engineering',
            'description' => null,
            'content' => ["type" => "text", "text" => "👋 Congrats on joining Hipper!"],
            'knowledgebase_id' => $knowledgebaseId,
            'topic_id' => $topicId,
        ];

        $kbResult = ['id' => $knowledgebaseId];
        $topicResult = ['id' => $topicId];
        $documentId = 'doc-uuid';
        $urlSlug = 'welcome-to-engineering';
        $urlId = 'url-id';
        $deducedDescription = '👋 Congrats on joining Hipper!';
        $contentPlain = '👋 Congrats on joining Hipper!';
        $rendererResult = new RendererResult;
        $rendererResult->setContent($contentPlain);
        $topicRoute = 'my/nested/topic';

        $documentRow = [
            'url_slug' => $urlSlug,
            'url_id' => $urlId,
        ];
        $topicRouteResult = ['route' => $topicRoute];
        $docRoute = $topicRoute . '/' . $urlSlug;
        $routeModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$parameters['knowledgebase_id'], $organizationId], $kbResult);
        $this->createTopicRepositoryExpectation([$topicId, $knowledgebaseId, $organizationId], $topicResult);
        $this->createDocumentValidatorExpectation(
            [$parameters, m::type(KnowledgebaseModel::class), m::type(TopicModel::class), true]
        );
        $this->createIdGeneratorExpectation($documentId);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $urlSlug);
        $this->createUrlIdGeneratorExpectation($urlId);
        $this->createDocumentDescriptionDeducerExpectation([$parameters['content']], $deducedDescription);
        $this->createDocumentRendererExpectation([json_encode($parameters['content']), 'text'], $rendererResult);
        $this->createConnectionBeginTransactionExpectation();
        $this->createDocumentInserterExpectation(
            [
                $documentId,
                $parameters['name'],
                $urlSlug,
                $urlId,
                $knowledgebaseId,
                $organizationId,
                $personId,
                $parameters['description'],
                $deducedDescription,
                json_encode($parameters['content']),
                $contentPlain,
                $topicId
            ],
            $documentRow
        );
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $topicId],
            $topicRouteResult
        );
        $this->createKnowledgebaseRouteCreatorExpectation(
            [m::type(DocumentModel::class), $docRoute, true, true],
            $routeModel
        );
        $this->createDocumentRevisionCreatorExpectation([m::type(DocumentModel::class)]);
        $this->createConnectionCommitExpectation();
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);

        $result = $this->documentCreator->create($person, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
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

    private function createKnowledgebaseRouteCreatorExpectation($args, $result)
    {
        $this->knowledgebaseRouteCreator
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

    private function createDocumentInserterExpectation($args, $result)
    {
        $this->documentInserter
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
