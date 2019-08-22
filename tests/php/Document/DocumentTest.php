<?php
declare(strict_types=1);

namespace Hipper\Tests\Document;

use Doctrine\DBAL\Connection;
use Hipper\Document\Document;
use Hipper\Document\DocumentDescriptionDeducer;
use Hipper\Document\DocumentInserter;
use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRevision;
use Hipper\Document\DocumentValidator;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Person\PersonModel;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    private $connection;
    private $documentDescriptionDeducer;
    private $documentInserter;
    private $documentRevision;
    private $documentValidator;
    private $idGenerator;
    private $knowledgebaseRoute;
    private $urlIdGenerator;
    private $urlSlugGenerator;
    private $document;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->documentDescriptionDeducer = m::mock(DocumentDescriptionDeducer::class);
        $this->documentInserter = m::mock(DocumentInserter::class);
        $this->documentRevision = m::mock(DocumentRevision::class);
        $this->documentValidator = m::mock(DocumentValidator::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->knowledgebaseRoute = m::mock(KnowledgebaseRoute::class);
        $this->urlIdGenerator = m::mock(UrlIdGenerator::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->document = new Document(
            $this->connection,
            $this->documentDescriptionDeducer,
            $this->documentInserter,
            $this->documentRevision,
            $this->documentValidator,
            $this->idGenerator,
            $this->knowledgebaseRoute,
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

        $documentId = 'doc-uuid';
        $urlSlug = 'welcome-to-engineering';
        $urlId = 'url-id';
        $deducedDescription = '👋 Congrats on joining Hipper!';

        $documentRow = [
            'url_slug' => $urlSlug,
            'url_id' => $urlId,
        ];
        $route = '/team/engineering/docs/~/welcome-to-engineering-url-id';

        $this->createDocumentValidatorExpectation([$parameters, $organizationId, true]);
        $this->createIdGeneratorExpectation($documentId);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $urlSlug);
        $this->createUrlIdGeneratorExpectation($urlId);
        $this->createDocumentDescriptionDeducerExpectation([$parameters['content']], $deducedDescription);
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
            ],
            $documentRow
        );
        $this->createKnowledgebaseRouteExpectation([m::type(DocumentModel::class), $urlSlug, true, true]);
        $this->createDocumentRevisionExpectation([m::type(DocumentModel::class)]);
        $this->createConnectionCommitExpectation();

        $result = $this->document->create($person, $parameters);
        $this->assertInstanceOf(DocumentModel::class, $result);
        $this->assertEquals($urlSlug, $result->getUrlSlug());
        $this->assertEquals($urlId, $result->getUrlId());
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

    private function createKnowledgebaseRouteExpectation($args)
    {
        $this->knowledgebaseRoute
            ->shouldReceive('createForDocument')
            ->once()
            ->with(...$args);
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
}
