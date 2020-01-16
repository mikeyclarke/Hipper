<?php
declare(strict_types=1);

namespace Hipper\Tests\Document;

use Doctrine\DBAL\Connection;
use Hipper\Document\Document;
use Hipper\Document\DocumentDescriptionDeducer;
use Hipper\Document\DocumentInserter;
use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRenderer;
use Hipper\Document\DocumentRevision;
use Hipper\Document\DocumentUpdater;
use Hipper\Document\DocumentValidator;
use Hipper\Document\Renderer\RendererResult;
use Hipper\Document\Exception\KnowledgebaseNotFoundException;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Person\PersonModel;
use Hipper\Section\SectionModel;
use Hipper\Section\SectionRepository;
use Hipper\Team\TeamModel;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $documentDescriptionDeducer;
    private $documentInserter;
    private $documentRenderer;
    private $documentRevision;
    private $documentUpdater;
    private $documentValidator;
    private $idGenerator;
    private $knowledgebaseOwner;
    private $knowledgebaseRepository;
    private $knowledgebaseRoute;
    private $knowledgebaseRouteRepository;
    private $sectionRepository;
    private $urlIdGenerator;
    private $urlSlugGenerator;
    private $document;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->documentDescriptionDeducer = m::mock(DocumentDescriptionDeducer::class);
        $this->documentInserter = m::mock(DocumentInserter::class);
        $this->documentRenderer = m::mock(DocumentRenderer::class);
        $this->documentRevision = m::mock(DocumentRevision::class);
        $this->documentUpdater = m::mock(DocumentUpdater::class);
        $this->documentValidator = m::mock(DocumentValidator::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->knowledgebaseOwner = m::mock(KnowledgebaseOwner::class);
        $this->knowledgebaseRepository = m::mock(KnowledgebaseRepository::class);
        $this->knowledgebaseRoute = m::mock(KnowledgebaseRoute::class);
        $this->knowledgebaseRouteRepository = m::mock(KnowledgebaseRouteRepository::class);
        $this->sectionRepository = m::mock(SectionRepository::class);
        $this->urlIdGenerator = m::mock(UrlIdGenerator::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->document = new Document(
            $this->connection,
            $this->documentDescriptionDeducer,
            $this->documentInserter,
            $this->documentRenderer,
            $this->documentRevision,
            $this->documentUpdater,
            $this->documentValidator,
            $this->idGenerator,
            $this->knowledgebaseOwner,
            $this->knowledgebaseRepository,
            $this->knowledgebaseRoute,
            $this->knowledgebaseRouteRepository,
            $this->sectionRepository,
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
        $this->createKnowledgebaseRouteExpectation([m::type(DocumentModel::class), $urlSlug, true, true], $routeModel);
        $this->createDocumentRevisionExpectation([m::type(DocumentModel::class)]);
        $this->createConnectionCommitExpectation();
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);

        $result = $this->document->create($person, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
    }

    /**
     * @test
     */
    public function createInSection()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $sectionId = 'section-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $parameters = [
            'name' => 'Welcome to Engineering',
            'description' => null,
            'content' => ["type" => "text", "text" => "👋 Congrats on joining Hipper!"],
            'knowledgebase_id' => $knowledgebaseId,
            'section_id' => $sectionId,
        ];

        $kbResult = ['id' => $knowledgebaseId];
        $sectionResult = ['id' => $sectionId];
        $documentId = 'doc-uuid';
        $urlSlug = 'welcome-to-engineering';
        $urlId = 'url-id';
        $deducedDescription = '👋 Congrats on joining Hipper!';
        $contentPlain = '👋 Congrats on joining Hipper!';
        $rendererResult = new RendererResult;
        $rendererResult->setContent($contentPlain);
        $sectionRoute = 'my/nested/section';

        $documentRow = [
            'url_slug' => $urlSlug,
            'url_id' => $urlId,
        ];
        $sectionRouteResult = ['route' => $sectionRoute];
        $docRoute = $sectionRoute . '/' . $urlSlug;
        $routeModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$parameters['knowledgebase_id'], $organizationId], $kbResult);
        $this->createSectionRepositoryExpectation([$sectionId, $knowledgebaseId, $organizationId], $sectionResult);
        $this->createDocumentValidatorExpectation(
            [$parameters, m::type(KnowledgebaseModel::class), m::type(SectionModel::class), true]
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
                $sectionId
            ],
            $documentRow
        );
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $sectionId],
            $sectionRouteResult
        );
        $this->createKnowledgebaseRouteExpectation([m::type(DocumentModel::class), $docRoute, true, true], $routeModel);
        $this->createDocumentRevisionExpectation([m::type(DocumentModel::class)]);
        $this->createConnectionCommitExpectation();
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);

        $result = $this->document->create($person, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
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
        $existingSectionId = 'section-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $document = new DocumentModel;
        $document->setId($documentId);
        $document->setName('My doc');
        $document->setKnowledgebaseId($knowledgebaseId);
        $document->setSectionId($existingSectionId);
        $document->setUrlSlug('my-doc');

        $parameters = [
            'name' => 'My updated doc',
        ];

        $kbResult = ['id' => $knowledgebaseId];
        $sectionResult = ['id' => $existingSectionId];
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
        $sectionRoute = 'my/nested/section';
        $sectionRouteResult = ['route' => $sectionRoute];
        $docRoute = $sectionRoute . '/' . $urlSlug;
        $routeModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $kbResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createDocumentValidatorExpectation([$parameters, null, null]);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $urlSlug);
        $this->createSectionRepositoryExpectation(
            [$existingSectionId, $knowledgebaseId, $organizationId],
            $sectionResult
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createDocumentUpdaterExpectation([$document->getId(), $propertiesToUpdate], $docUpdateResult);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $existingSectionId],
            $sectionRouteResult
        );
        $this->createKnowledgebaseRouteExpectation([$document, $docRoute, true], $routeModel);
        $this->createDocumentRevisionExpectation([$document]);
        $this->createConnectionCommitExpectation();

        $result = $this->document->update($person, $document, $parameters);
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
        $existingSectionId = 'prev-section-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $document = new DocumentModel;
        $document->setId($documentId);
        $document->setName('My doc');
        $document->setKnowledgebaseId($knowledgebaseId);
        $document->setSectionId($existingSectionId);
        $document->setUrlSlug('my-doc');

        $parameters = [
            'name' => 'My Doc',
        ];

        $kbResult = ['id' => $knowledgebaseId];
        $sectionResult = ['id' => $existingSectionId];
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
        $this->createDocumentUpdaterExpectation([$document->getId(), $propertiesToUpdate], $docUpdateResult);
        $this->createDocumentRevisionExpectation([$document]);
        $this->createConnectionCommitExpectation();

        $result = $this->document->update($person, $document, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
        $this->assertEquals($parameters['name'], $document->getName());
        $this->assertEquals($urlSlug, $document->getUrlSlug());
    }

    /**
     * @test
     */
    public function moveToNewSection()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $documentId = 'doc-uuid';
        $previousSectionId = 'prev-section-uuid';
        $sectionId = 'section-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $document = new DocumentModel;
        $document->setId($documentId);
        $document->setKnowledgebaseId($knowledgebaseId);
        $document->setSectionId($previousSectionId);
        $document->setUrlSlug('my-doc');

        $parameters = [
            'section_id' => $sectionId,
        ];

        $kbResult = ['id' => $knowledgebaseId];
        $sectionResult = ['id' => $sectionId];
        $propertiesToUpdate = [
            'last_updated_by' => $personId,
            'section_id' => $sectionId,
        ];
        $docUpdateResult = [
            'section_id' => $sectionId,
        ];
        $sectionRoute = 'my/nested/section';
        $sectionRouteResult = ['route' => $sectionRoute];
        $docRoute = $sectionRoute . '/' . $document->getUrlSlug();
        $routeModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $kbResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createSectionRepositoryExpectation([$sectionId, $knowledgebaseId, $organizationId], $sectionResult);
        $this->createDocumentValidatorExpectation(
            [$parameters, null, m::type(SectionModel::class)]
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createDocumentUpdaterExpectation([$document->getId(), $propertiesToUpdate], $docUpdateResult);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $sectionId],
            $sectionRouteResult
        );
        $this->createKnowledgebaseRouteExpectation([$document, $docRoute, true], $routeModel);
        $this->createDocumentRevisionExpectation([$document]);
        $this->createConnectionCommitExpectation();

        $result = $this->document->update($person, $document, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
        $this->assertEquals($sectionId, $document->getSectionId());
    }

    /**
     * @test
     */
    public function updateNameWhilstMovingToNewSection()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $documentId = 'doc-uuid';
        $previousSectionId = 'prev-section-uuid';
        $sectionId = 'section-uuid';

        $person = new PersonModel;
        $person->setId($personId);
        $person->setOrganizationId($organizationId);

        $document = new DocumentModel;
        $document->setId($documentId);
        $document->setName('My doc');
        $document->setKnowledgebaseId($knowledgebaseId);
        $document->setSectionId($previousSectionId);
        $document->setUrlSlug('my-doc');

        $parameters = [
            'name' => 'My updated doc',
            'section_id' => $sectionId,
        ];

        $kbResult = ['id' => $knowledgebaseId];
        $sectionResult = ['id' => $sectionId];
        $urlSlug = 'my-updated-doc';
        $propertiesToUpdate = [
            'last_updated_by' => $personId,
            'name' => $parameters['name'],
            'section_id' => $sectionId,
            'url_slug' => $urlSlug,
        ];
        $docUpdateResult = [
            'name' => $parameters['name'],
            'section_id' => $sectionId,
            'url_slug' => $urlSlug,
        ];
        $sectionRoute = 'my/nested/section';
        $sectionRouteResult = ['route' => $sectionRoute];
        $docRoute = $sectionRoute . '/' . $urlSlug;
        $routeModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new TeamModel;

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $kbResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createSectionRepositoryExpectation([$sectionId, $knowledgebaseId, $organizationId], $sectionResult);
        $this->createDocumentValidatorExpectation(
            [$parameters, null, m::type(SectionModel::class)]
        );
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $urlSlug);
        $this->createConnectionBeginTransactionExpectation();
        $this->createDocumentUpdaterExpectation([$document->getId(), $propertiesToUpdate], $docUpdateResult);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $sectionId],
            $sectionRouteResult
        );
        $this->createKnowledgebaseRouteExpectation([$document, $docRoute, true], $routeModel);
        $this->createDocumentRevisionExpectation([$document]);
        $this->createConnectionCommitExpectation();

        $result = $this->document->update($person, $document, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[0]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[1]);
        $this->assertEquals($sectionId, $document->getSectionId());
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
        $this->createDocumentUpdaterExpectation([$document->getId(), $propertiesToUpdate], $docUpdateResult);
        $this->createDocumentRevisionExpectation([$document]);
        $this->createConnectionCommitExpectation();

        $result = $this->document->update($person, $document, $parameters);
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
        $this->createDocumentUpdaterExpectation([$document->getId(), $propertiesToUpdate], $docUpdateResult);
        $this->createDocumentRevisionExpectation([$document]);
        $this->createConnectionCommitExpectation();

        $result = $this->document->update($person, $document, $parameters);
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

        $result = $this->document->update($person, $document, $parameters);
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
        $this->document->update($person, $document, $parameters);
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

    private function createDocumentRevisionExpectation($args)
    {
        $this->documentRevision
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
            ->shouldReceive('findCanonicalRouteForSection')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createDocumentUpdaterExpectation($args, $result)
    {
        $this->documentUpdater
            ->shouldReceive('update')
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

    private function createSectionRepositoryExpectation($args, $result)
    {
        $this->sectionRepository
            ->shouldReceive('findById')
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
