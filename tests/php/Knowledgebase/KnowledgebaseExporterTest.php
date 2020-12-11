<?php

declare(strict_types=1);

namespace Hipper\Tests\Knowledgebase;

use Hipper\Document\DocumentExporter;
use Hipper\Document\DocumentModel;
use Hipper\File\FileNameGenerator;
use Hipper\Filesystem\FilesystemFactory;
use Hipper\Knowledgebase\KnowledgebaseExporter;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class KnowledgebaseExporterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $documentExporter;
    private $fileNameGenerator;
    private $filesystemFactory;
    private $knowledgebaseRepository;
    private $knowledgebaseExporter;
    private $filesystem;

    public function setUp(): void
    {
        $this->documentExporter = m::mock(DocumentExporter::class);
        $this->fileNameGenerator = m::mock(FileNameGenerator::class);
        $this->filesystemFactory = m::mock(FilesystemFactory::class);
        $this->knowledgebaseRepository = m::mock(KnowledgebaseRepository::class);

        $this->knowledgebaseExporter = new KnowledgebaseExporter(
            $this->documentExporter,
            $this->fileNameGenerator,
            $this->filesystemFactory,
            $this->knowledgebaseRepository
        );

        $this->filesystem = m::mock(Filesystem::class);
    }

    /**
     * @test
     */
    public function export()
    {
        $knowledgebaseId = 'kb-uuid';
        $organizationId = 'org-uuid';
        $knowledgebase = KnowledgebaseModel::createFromArray([
            'id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $organizationDomain = 'acme.usehipper.test';
        $directoryPathname = '/tmp';

        $contents = [
            [
                'id' => 'node1-uuid',
                'name' => 'Doc 1',
                'parent_topic_id' => null,
                'type' => 'document',
            ],
            [
                'id' => 'node2-uuid',
                'name' => 'Topic 1',
                'parent_topic_id' => null,
                'type' => 'topic',
            ],
            [
                'id' => 'node3-uuid',
                'name' => 'Topic 1 subtopic',
                'parent_topic_id' => 'node2-uuid',
                'type' => 'topic',
            ],
            [
                'id' => 'node4-uuid',
                'name' => 'Topic 1 doc',
                'parent_topic_id' => 'node2-uuid',
                'type' => 'document',
            ],
            [
                'id' => 'node5-uuid',
                'name' => 'Topic 1 doc',
                'parent_topic_id' => 'node2-uuid',
                'type' => 'document',
            ],
            [
                'id' => 'node6-uuid',
                'name' => 'Topic 1 subtopic doc 1',
                'parent_topic_id' => 'node3-uuid',
                'type' => 'document',
            ],
            [
                'id' => 'node7-uuid',
                'name' => 'Topic 1 subtopic doc 2',
                'parent_topic_id' => 'node3-uuid',
                'type' => 'document',
            ],
        ];
        $node1Content = 'node 1 content';
        $node1FileName = 'Node 1.md';
        $node4Content = 'node 4 content';
        $node4FileName = 'Topic 1 doc.md';
        $node5Content = 'node 5 content';
        $node5FileName = 'Topic 1 doc.md';
        $node6Content = 'node 6 content';
        $node6FileName = 'Node 6.md';
        $node7Content = 'node 7 content';
        $node7FileName = 'Node 7.md';

        $this->createFilesystemFactoryExpectation();
        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $contents);

        $this->createDocumentExporterExpectation(
            [m::type(DocumentModel::class), $organizationDomain],
            [$node1Content, $node1FileName]
        );
        $this->createFilesystemExistsExpectation(
            [$directoryPathname . '/' . $node1FileName],
            false
        );
        $this->createFilesystemDumpFileExpectation([$directoryPathname . '/' . $node1FileName, $node1Content]);

        $this->createFileNameGeneratorExpectation(['Topic 1'], 'Topic 1');
        $this->createFilesystemMkdirExpectation([$directoryPathname . '/Topic 1']);

        $this->createFileNameGeneratorExpectation(['Topic 1 subtopic'], 'Topic 1 subtopic');
        $this->createFilesystemMkdirExpectation([$directoryPathname . '/Topic 1/Topic 1 subtopic']);

        $this->createDocumentExporterExpectation(
            [m::type(DocumentModel::class), $organizationDomain],
            [$node6Content, $node6FileName]
        );
        $this->createFilesystemExistsExpectation(
            [$directoryPathname . '/Topic 1/Topic 1 subtopic/' . $node6FileName],
            false
        );
        $this->createFilesystemDumpFileExpectation(
            [$directoryPathname . '/Topic 1/Topic 1 subtopic/' . $node6FileName, $node6Content]
        );

        $this->createDocumentExporterExpectation(
            [m::type(DocumentModel::class), $organizationDomain],
            [$node7Content, $node7FileName]
        );
        $this->createFilesystemExistsExpectation(
            [$directoryPathname . '/Topic 1/Topic 1 subtopic/' . $node7FileName],
            false
        );
        $this->createFilesystemDumpFileExpectation(
            [$directoryPathname . '/Topic 1/Topic 1 subtopic/' . $node7FileName, $node7Content]
        );

        $this->createDocumentExporterExpectation(
            [m::type(DocumentModel::class), $organizationDomain],
            [$node4Content, $node4FileName]
        );
        $this->createFilesystemExistsExpectation(
            [$directoryPathname . '/Topic 1/' . $node4FileName],
            false
        );
        $this->createFilesystemDumpFileExpectation([$directoryPathname . '/Topic 1/' . $node4FileName, $node4Content]);

        $this->createDocumentExporterExpectation(
            [m::type(DocumentModel::class), $organizationDomain],
            [$node5Content, $node5FileName]
        );
        $this->createFilesystemExistsExpectation(
            [$directoryPathname . '/Topic 1/' . $node5FileName],
            true
        );
        $this->createFilesystemExistsExpectation(
            [$directoryPathname . '/Topic 1/Topic 1 doc (1).md'],
            false
        );
        $this->createFilesystemDumpFileExpectation([$directoryPathname . '/Topic 1/Topic 1 doc (1).md', $node5Content]);

        $result = $this->knowledgebaseExporter->export($knowledgebase, $organizationDomain, $directoryPathname);
        $this->assertEquals($directoryPathname, $result);
    }

    private function createFilesystemFactoryExpectation()
    {
        $this->filesystemFactory
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->filesystem);
    }

    private function createKnowledgebaseRepositoryExpectation($args, $result)
    {
        $this->knowledgebaseRepository
            ->shouldReceive('getContents')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createDocumentExporterExpectation($args, $result)
    {
        $this->documentExporter
            ->shouldReceive('export')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFilesystemExistsExpectation($args, $result)
    {
        $this->filesystem
            ->shouldReceive('exists')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFilesystemDumpFileExpectation($args)
    {
        $this->filesystem
            ->shouldReceive('dumpFile')
            ->once()
            ->with(...$args);
    }

    private function createFileNameGeneratorExpectation($args, $result)
    {
        $this->fileNameGenerator
            ->shouldReceive('generateFromString')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFilesystemMkdirExpectation($args)
    {
        $this->filesystem
            ->shouldReceive('mkdir')
            ->once()
            ->with(...$args);
    }
}
