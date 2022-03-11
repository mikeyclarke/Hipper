<?php

declare(strict_types=1);

namespace Hipper\Tests\File;

use Hipper\File\FileDeleter;
use Hipper\File\FileModel;
use Hipper\File\FileWriter;
use Hipper\File\Storage\FileDeleter as FileStorageDeleter;
use Hipper\File\Storage\FileUpdater as FileStorageUpdater;
use Hipper\Messenger\MessageBus;
use Hipper\Messenger\Message\FilesMarkedForDeletion;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class FileDeleterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $fileStorageDeleter;
    private $fileStorageUpdater;
    private $fileWriter;
    private $messageBus;
    private $fileDeleter;

    public function setUp(): void
    {
        $this->fileStorageDeleter = m::mock(FileStorageDeleter::class);
        $this->fileStorageUpdater = m::mock(FileStorageUpdater::class);
        $this->fileWriter = m::mock(FileWriter::class);
        $this->messageBus = m::mock(MessageBus::class);

        $this->fileDeleter = new FileDeleter(
            $this->fileStorageDeleter,
            $this->fileStorageUpdater,
            $this->fileWriter,
            $this->messageBus,
        );
    }

    /**
     * @test
     */
    public function markForDeletion()
    {
        $fileIds = ['file1uuid', 'file2uuid', 'file3uuid'];

        $this->createFileStorageUpdaterExpectation([
            ['marked_for_deletion' => true],
            ['id' => $fileIds],
        ]);
        $this->createMessageBusExpectation([m::type(FilesMarkedForDeletion::class)]);

        $this->fileDeleter->markForDeletion($fileIds);
    }

    /**
     * @test
     */
    public function deleteFile()
    {
        $fileId = 'file-uuid';
        $fileStoragePath = 'path/file.jpg';
        $file = FileModel::createFromArray([
            'id' => $fileId,
            'storage_path' => $fileStoragePath,
        ]);

        $this->createFileWriterExpectation([$fileStoragePath]);
        $this->createFileStorageDeleterExpectation([$fileId]);

        $this->fileDeleter->deleteFile($file);
    }

    private function createFileStorageUpdaterExpectation($args)
    {
        $this->fileStorageUpdater
            ->shouldReceive('bulkUpdate')
            ->once()
            ->with(...$args);
    }

    private function createMessageBusExpectation($args)
    {
        $this->messageBus
            ->shouldReceive('dispatch')
            ->once()
            ->with(...$args);
    }

    private function createFileStorageDeleterExpectation($args)
    {
        $this->fileStorageDeleter
            ->shouldReceive('delete')
            ->once()
            ->with(...$args);
    }

    private function createFileWriterExpectation($args)
    {
        $this->fileWriter
            ->shouldReceive('delete')
            ->once()
            ->with(...$args);
    }
}
