<?php

declare(strict_types=1);

namespace Hipper\Tests\File;

use Hipper\File\FileWriter;
use League\Flysystem\Filesystem;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class FileWriterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $filesystem;
    private $fileWriter;

    public function setUp(): void
    {
        $this->filesystem = m::mock(Filesystem::class);

        $this->fileWriter = new FileWriter(
            $this->filesystem
        );
    }

    /**
     * @test
     */
    public function write()
    {
        $storagePath = 'profile-image/foo.jpeg';
        $contents = 'file';

        $this->createFilesystemWriteExpectation([$storagePath, $contents]);

        $this->fileWriter->write($storagePath, $contents);
    }

    /**
     * @test
     */
    public function delete()
    {
        $storagePath = 'profile-image/foo.jpeg';

        $this->createFilesystemDeleteExpectation([$storagePath]);

        $this->fileWriter->delete($storagePath);
    }

    private function createFilesystemWriteExpectation($args)
    {
        $this->filesystem
            ->shouldReceive('write')
            ->once()
            ->with(...$args);
    }

    private function createFilesystemDeleteExpectation($args)
    {
        $this->filesystem
            ->shouldReceive('delete')
            ->once()
            ->with(...$args);
    }
}
