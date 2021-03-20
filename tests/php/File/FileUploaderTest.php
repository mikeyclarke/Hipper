<?php

declare(strict_types=1);

namespace Hipper\Tests\File;

use Hipper\File\Exception\UnguessableFileExtensionException;
use Hipper\File\FileModel;
use Hipper\File\FileTypeGuesser;
use Hipper\File\FileUploader;
use Hipper\File\FileWriter;
use Hipper\File\StoragePathGenerator;
use Hipper\File\Storage\FileInserter;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Image\ImageResource\ImageResourceFactory;
use Hipper\Image\ImageResource\ImageResourceInterface;
use Hipper\Person\PersonModel;
use Mockery as m;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $fileInserter;
    private $fileTypeGuesser;
    private $fileWriter;
    private $idGenerator;
    private $imageResourceFactory;
    private $storagePathGenerator;
    private $fileUploader;
    private $imageResource;

    public function setUp(): void
    {
        $this->fileInserter = m::mock(FileInserter::class);
        $this->fileTypeGuesser = m::mock(FileTypeGuesser::class);
        $this->fileWriter = m::mock(FileWriter::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->imageResourceFactory = m::mock(ImageResourceFactory::class);
        $this->storagePathGenerator = m::mock(StoragePathGenerator::class);

        $this->fileUploader = new FileUploader(
            $this->fileInserter,
            $this->fileTypeGuesser,
            $this->fileWriter,
            $this->idGenerator,
            $this->imageResourceFactory,
            $this->storagePathGenerator,
        );

        $this->imageResource = m::mock(ImageResourceInterface::class);

        vfsStream::setup('uploads');
    }

    /**
     * @test
     */
    public function uploadImageTypeFile()
    {
        $organizationId = 'org-uuid';
        $personId = 'person-uuid';
        $person = PersonModel::createFromArray([
            'id' => $personId,
            'organization_id' => $organizationId,
        ]);

        $path = __DIR__ . '/../../data/puppers.jpeg';

        $uploadedFile = new UploadedFile($path, originalName: 'puppers', test: true);
        $extension = $uploadedFile->guessExtension();
        $mimeType = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();
        $pathname = $uploadedFile->getPathname();

        $usage = 'profile_image';

        $id = 'file-uuid';
        $storagePath = sprintf('profile-image/%s.%s', $id, $extension);
        $fileType = 'image';
        $contentHash = md5_file($path);
        $height = 1500;
        $width = 1000;
        $insertResult = [
            'id' => $id,
            'storage_path' => $storagePath,
        ];

        $this->createIdGeneratorExpectation($id);
        $this->createStoragePathGeneratorExpectation([$usage, $id, $extension], $storagePath);
        $this->createFileTypeGuesserExpectation([$mimeType], $fileType);
        $this->createImageResourceFactoryExpectation([$pathname, $mimeType]);
        $this->createImageResourceGetHeightExpectation($height);
        $this->createImageResourceGetWidthExpectation($width);
        $this->createFileInserterExpectation(
            [
                $id,
                $contentHash,
                $storagePath,
                $fileType,
                $mimeType,
                $usage,
                $size,
                $height,
                $width,
                $organizationId,
                $personId
            ],
            $insertResult
        );
        $this->createFileWriterExpectation([$storagePath, $uploadedFile->getContent()]);

        $result = $this->fileUploader->upload($person, $uploadedFile, $usage);
        $this->assertInstanceOf(FileModel::class, $result);
    }

    /**
     * @test
     */
    public function uploadNonImageTypeFile()
    {
        $organizationId = 'org-uuid';
        $personId = 'person-uuid';
        $person = PersonModel::createFromArray([
            'id' => $personId,
            'organization_id' => $organizationId,
        ]);

        $path = __DIR__ . '/../../data/puppers.jpeg';

        $uploadedFile = new UploadedFile($path, originalName: 'puppers', test: true);
        $extension = $uploadedFile->guessExtension();
        $mimeType = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();
        $pathname = $uploadedFile->getPathname();

        $usage = 'profile_image';

        $id = 'file-uuid';
        $storagePath = sprintf('profile-image/%s.%s', $id, $extension);
        $fileType = 'document';
        $contentHash = md5_file($path);
        $height = null;
        $width = null;
        $insertResult = [
            'id' => $id,
            'storage_path' => $storagePath,
        ];

        $this->createIdGeneratorExpectation($id);
        $this->createStoragePathGeneratorExpectation([$usage, $id, $extension], $storagePath);
        $this->createFileTypeGuesserExpectation([$mimeType], $fileType);
        $this->createFileInserterExpectation(
            [
                $id,
                $contentHash,
                $storagePath,
                $fileType,
                $mimeType,
                $usage,
                $size,
                $height,
                $width,
                $organizationId,
                $personId
            ],
            $insertResult
        );
        $this->createFileWriterExpectation([$storagePath, $uploadedFile->getContent()]);

        $result = $this->fileUploader->upload($person, $uploadedFile, $usage);
        $this->assertInstanceOf(FileModel::class, $result);
    }

    /**
     * @test
     */
    public function unguessableExtension()
    {
        $organizationId = 'org-uuid';
        $personId = 'person-uuid';
        $person = PersonModel::createFromArray([
            'id' => $personId,
            'organization_id' => $organizationId,
        ]);

        $path = 'vfs://uploads/foo';
        touch($path);

        $uploadedFile = new UploadedFile($path, originalName: 'foo', test: true);
        $usage = 'profile_image';

        $this->expectException(UnguessableFileExtensionException::class);

        $this->fileUploader->upload($person, $uploadedFile, $usage);
    }

    private function createIdGeneratorExpectation($result)
    {
        $this->idGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }

    private function createStoragePathGeneratorExpectation($args, $result)
    {
        $this->storagePathGenerator
            ->shouldReceive('generate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFileTypeGuesserExpectation($args, $result)
    {
        $this->fileTypeGuesser
            ->shouldReceive('guessFromMimeType')
            ->once()
            ->with(...$args)
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
