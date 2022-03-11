<?php

declare(strict_types=1);

namespace Hipper\Tests\Messenger\MessageHandler;

use Doctrine\DBAL\Connection;
use Hipper\File\FileHash;
use Hipper\File\FileModel;
use Hipper\File\FileReader;
use Hipper\File\FileRepository;
use Hipper\File\FileWriter;
use Hipper\File\Processor\ProcessorInterface;
use Hipper\File\StoragePathGenerator;
use Hipper\File\Storage\FileUpdater;
use Hipper\File\TempFileWriter;
use Hipper\Messenger\MessageHandler\FileUploadedHandler;
use Hipper\Messenger\Message\FileUploaded;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class FileUploadedHandlerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $fileHash;
    private $fileReader;
    private $fileRepository;
    private $fileUpdater;
    private $fileWriter;
    private $storagePathGenerator;
    private $tempFileWriter;
    private $fileBaseUrl;
    private $fileUploadedHandler;
    private $fooProcessor;
    private $barProcessor;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->fileHash = m::mock(FileHash::class);
        $this->fileReader = m::mock(FileReader::class);
        $this->fileRepository = m::mock(FileRepository::class);
        $this->fileUpdater = m::mock(FileUpdater::class);
        $this->fileWriter = m::mock(FileWriter::class);
        $this->storagePathGenerator = m::mock(StoragePathGenerator::class);
        $this->tempFileWriter = m::mock(TempFileWriter::class);
        $this->fileBaseUrl = 'https://cdn.usehipper.test/';

        $this->fileUploadedHandler = new FileUploadedHandler(
            $this->connection,
            $this->fileHash,
            $this->fileReader,
            $this->fileRepository,
            $this->fileUpdater,
            $this->fileWriter,
            $this->storagePathGenerator,
            $this->tempFileWriter,
            $this->fileBaseUrl,
        );

        $this->fooProcessor = m::mock(ProcessorInterface::class);
        $this->barProcessor = m::mock(ProcessorInterface::class);

        $this->fileUploadedHandler->registerProcessor($this->fooProcessor);
        $this->fileUploadedHandler->registerProcessor($this->barProcessor);
    }

    public function testInvoke()
    {
        $fileId = 'file-uuid';
        $usage = 'foo';

        $message = new FileUploaded(
            $fileId,
            $usage,
        );

        $originalContentHash = 'original-hash';
        $originalStoragePath = 'original/storage_path.jpeg';
        $fileResult = [
            'id' => $fileId,
            'usage' => $usage,
            'content_hash' => $originalContentHash,
            'storage_path' => $originalStoragePath,
        ];
        $originalFileContents = 'original-file-contents';
        $tempPathname = '/var/file_upload_processor_' . $fileId;
        $newStoragePath = 'new/storage_path.jpeg';
        $newContentHash = 'new-hash';
        $newFileContents = 'new-file-contents';

        $this->createFileRepositoryExpectation([$fileId, ['content_hash']], $fileResult);
        $this->createFileReaderExpectation(
            [sprintf('%s%s', $this->fileBaseUrl, $originalStoragePath)],
            $originalFileContents
        );
        $this->createTempFileWriterWriteExpectation(
            [sprintf('file_upload_processor_%s', $fileId), $originalFileContents],
            $tempPathname
        );
        $this->createFooProcessorCanProcessFileExpectation([m::type(FileModel::class)], false);
        $this->createBarProcessorCanProcessFileExpectation([m::type(FileModel::class)], true);
        $this->createBarProcessorProccessExpectation([m::type(FileModel::class), $tempPathname]);
        $this->createFileHashEqualsExpectation([$tempPathname, $fileResult['content_hash']], false);
        $this->createStoragePathGeneratorExpectation([$usage, 'jpeg'], $newStoragePath);
        $this->createFileHashGetExpectation([$tempPathname], $newContentHash);
        $this->createConnectionBeginTransactionExpectation();
        $this->createFileUpdaterExpectation([
            $fileId,
            ['content_hash' => $newContentHash, 'storage_path' => $newStoragePath, 'processing' => 'completed']
        ]);
        $this->createFileReaderExpectation([$tempPathname], $newFileContents);
        $this->createFileWriterWriteExpectation([$newStoragePath, $newFileContents]);
        $this->createFileWriterDeleteExpectation([$originalStoragePath]);
        $this->createConnectionCommitExpectation();
        $this->createTempFileWriterRemoveExpectation([$tempPathname]);

        $this->fileUploadedHandler->__invoke($message);
    }

    public function testInvokeWhenProcessorsDoNotAlterFile()
    {
        $fileId = 'file-uuid';
        $usage = 'foo';

        $message = new FileUploaded(
            $fileId,
            $usage,
        );

        $originalContentHash = 'original-hash';
        $originalStoragePath = 'original/storage_path.jpeg';
        $fileResult = [
            'id' => $fileId,
            'usage' => $usage,
            'content_hash' => $originalContentHash,
            'storage_path' => $originalStoragePath,
        ];
        $originalFileContents = 'original-file-contents';
        $tempPathname = '/var/file_upload_processor_' . $fileId;

        $this->createFileRepositoryExpectation([$fileId, ['content_hash']], $fileResult);
        $this->createFileReaderExpectation(
            [sprintf('%s%s', $this->fileBaseUrl, $originalStoragePath)],
            $originalFileContents
        );
        $this->createTempFileWriterWriteExpectation(
            [sprintf('file_upload_processor_%s', $fileId), $originalFileContents],
            $tempPathname
        );
        $this->createFooProcessorCanProcessFileExpectation([m::type(FileModel::class)], false);
        $this->createBarProcessorCanProcessFileExpectation([m::type(FileModel::class)], true);
        $this->createBarProcessorProccessExpectation([m::type(FileModel::class), $tempPathname]);
        $this->createFileHashEqualsExpectation([$tempPathname, $fileResult['content_hash']], true);
        $this->createTempFileWriterRemoveExpectation([$tempPathname]);
        $this->createFileUpdaterExpectation([$fileId, ['processing' => 'completed']]);

        $this->fileUploadedHandler->__invoke($message);
    }

    private function createFileRepositoryExpectation($args, $result)
    {
        $this->fileRepository
            ->shouldReceive('findById')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFileReaderExpectation($args, $result)
    {
        $this->fileReader
            ->shouldReceive('read')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createTempFileWriterWriteExpectation($args, $result)
    {
        $this->tempFileWriter
            ->shouldReceive('write')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFooProcessorCanProcessFileExpectation($args, $result)
    {
        $this->fooProcessor
            ->shouldReceive('canProcessFile')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createBarProcessorCanProcessFileExpectation($args, $result)
    {
        $this->barProcessor
            ->shouldReceive('canProcessFile')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createBarProcessorProccessExpectation($args)
    {
        $this->barProcessor
            ->shouldReceive('process')
            ->once()
            ->with(...$args);
    }

    private function createFileHashEqualsExpectation($args, $result)
    {
        $this->fileHash
            ->shouldReceive('equals')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFileHashGetExpectation($args, $result)
    {
        $this->fileHash
            ->shouldReceive('get')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createTempFileWriterRemoveExpectation($args)
    {
        $this->tempFileWriter
            ->shouldReceive('remove')
            ->once()
            ->with(...$args);
    }

    private function createFileUpdaterExpectation($args)
    {
        $this->fileUpdater
            ->shouldReceive('update')
            ->once()
            ->with(...$args);
    }

    private function createStoragePathGeneratorExpectation($args, $result)
    {
        $this->storagePathGenerator
            ->shouldReceive('generate')
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

    private function createFileWriterWriteExpectation($args)
    {
        $this->fileWriter
            ->shouldReceive('write')
            ->once()
            ->with(...$args);
    }

    private function createConnectionCommitExpectation()
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }

    private function createFileWriterDeleteExpectation($args)
    {
        $this->fileWriter
            ->shouldReceive('delete')
            ->once()
            ->with(...$args);
    }
}
