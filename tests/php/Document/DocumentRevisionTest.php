<?php
declare(strict_types=1);

namespace Hipper\Tests\Document;

use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRevision;
use Hipper\Document\Storage\DocumentRevisionInserter;
use Hipper\IdGenerator\IdGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DocumentRevisionTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $documentRevisionInserter;
    private $idGenerator;
    private $documentRevision;

    public function setUp(): void
    {
        $this->documentRevisionInserter = m::mock(DocumentRevisionInserter::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->documentRevision = new DocumentRevision(
            $this->documentRevisionInserter,
            $this->idGenerator
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $model = new DocumentModel;
        $model->setId('doc-uuid');
        $model->setName('doc-name');
        $model->setOrganizationId('org-uuid');
        $model->setKnowledgebaseId('kb-uuid');
        $model->setCreatedBy('created-by-uuid');
        $model->setDescription('description');
        $model->setDeducedDescription('deduced-description');
        $model->setContent('content');

        $revisionId = 'revision-uuid';

        $this->createIdGeneratorExpectation($revisionId);
        $this->createDocumentRevisionInserterExpectation([
            'revision-uuid',
            'doc-uuid',
            'doc-name',
            'org-uuid',
            'kb-uuid',
            'created-by-uuid',
            'description',
            'deduced-description',
            'content',
        ]);

        $this->documentRevision->create($model);
    }

    private function createDocumentRevisionInserterExpectation($args)
    {
        $this->documentRevisionInserter
            ->shouldReceive('insert')
            ->once()
            ->with(...$args);
    }

    private function createIdGeneratorExpectation($result)
    {
        $this->idGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }
}
