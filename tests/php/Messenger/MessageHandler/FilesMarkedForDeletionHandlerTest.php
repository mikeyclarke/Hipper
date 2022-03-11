<?php

declare(strict_types=1);

namespace Hipper\Tests\Messenger\MessageHandler;

use Hipper\File\FileDeleter;
use Hipper\File\FileModel;
use Hipper\File\FileRepository;
use Hipper\Messenger\MessageHandler\FilesMarkedForDeletionHandler;
use Hipper\Messenger\Message\FilesMarkedForDeletion;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class FilesMarkedForDeletionHandlerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $fileDeleter;
    private $fileRepository;
    private $handler;

    public function setUp(): void
    {
        $this->fileDeleter = m::mock(FileDeleter::class);
        $this->fileRepository = m::mock(FileRepository::class);

        $this->handler = new FilesMarkedForDeletionHandler(
            $this->fileDeleter,
            $this->fileRepository,
        );
    }

    /**
     * @test
     */
    public function invoke()
    {
        $fileIds = ['file1uuid', 'file2uuid', 'file3uuid'];

        $message = new FilesMarkedForDeletion($fileIds);

        $file1Result = ['id' => $fileIds[0]];
        $file2Result = ['id' => $fileIds[1]];
        $file3Result = ['id' => $fileIds[2]];

        $this->createFileRepositoryExpectation([$fileIds[0]], $file1Result);
        $this->createFileDeleterExpectation([m::type(FileModel::class)]);

        $this->createFileRepositoryExpectation([$fileIds[1]], $file2Result);
        $this->createFileDeleterExpectation([m::type(FileModel::class)]);

        $this->createFileRepositoryExpectation([$fileIds[2]], $file3Result);
        $this->createFileDeleterExpectation([m::type(FileModel::class)]);

        $this->handler->__invoke($message);
    }

    private function createFileRepositoryExpectation($args, $result)
    {
        $this->fileRepository
            ->shouldReceive('findById')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFileDeleterExpectation($args)
    {
        $this->fileDeleter
            ->shouldReceive('deleteFile')
            ->once()
            ->with(...$args);
    }
}
