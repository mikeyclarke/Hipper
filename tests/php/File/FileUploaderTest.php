<?php

declare(strict_types=1);

namespace Hipper\Tests\File;

use Hipper\File\FileUploader;
use Hipper\File\FileWriter;
use Hipper\File\Storage\FileInserter;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Image\ImageResource\ImageResourceFactory;
use Hipper\Image\ImageResource\ImageResourceInterface;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $fileInserter;
    private $fileWriter;
    private $idGenerator;
    private $imageResourceFactory;
    private $fileUploader;
    private $imageResource;

    public function setUp(): void
    {
        $this->fileInserter = m::mock(FileInserter::class);
        $this->fileWriter = m::mock(FileWriter::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->imageResourceFactory = m::mock(ImageResourceFactory::class);

        $this->fileUploader = new FileUploader(
            $this->fileInserter,
            $this->fileWriter,
            $this->idGenerator,
            $this->imageResourceFactory
        );

        $this->imageResource = m::mock(ImageResourceInterface::class);
    }

    public function uploadTest()
    {
        $person = PersonModel::createFromArray([
            
        ]);
        $uploadedFile = new UploadedFile();

        $this->createIdGeneratorExpectation($id);
        $this->createImageResourceFactoryExpectation($args);
        $this->createImageResourceGetHeightExpectation($result);
        $this->createImageResourceGetWidthExpectation($result);
        $this->createFileInserterExpectation($args, $result);
        $this->createFileWriterExpectation($args);

        $result = $this->fileUploader->upload($person, $uploadedFile, $usage);
    }

    private function createIdGeneratorExpectation($result)
    {
        $this->idGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }

    private function createImageResourceFactoryExpectation($args)
    {
        $this->imageResourceFactory
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($this->imageResource);
    }

    private function createImageResourceGetHeightExpectation($result)
    {
        $this->imageResource
            ->shouldReceive('getHeight')
            ->once()
            ->andReturn($result);
    }

    private function createImageResourceGetWidthExpectation($result)
    {
        $this->imageResource
            ->shouldReceive('getWidth')
            ->once()
            ->andReturn($result);
    }

    private function createFileInserterExpectation($args, $result)
    {
        $this->fileInserter
            ->shouldReceive('insert')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFileWriterExpectation($args)
    {
        $this->fileWriter
            ->shouldReceive('write')
            ->once()
            ->with(...$args);
    }
}
