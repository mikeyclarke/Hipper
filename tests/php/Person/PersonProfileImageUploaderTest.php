<?php

declare(strict_types=1);

namespace Hipper\Tests\Person;

use Doctrine\DBAL\Connection;
use Hipper\File\FileDeleter;
use Hipper\File\FileModel;
use Hipper\File\FileUploader;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonProfileImageUploader;
use Hipper\Person\PersonProfileImageValidator;
use Hipper\Person\Storage\PersonUpdater as PersonStorageUpdater;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PersonProfileImageUploaderTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $fileDeleter;
    private $fileUploader;
    private $validator;
    private $personStorageUpdater;
    private $profileImageUploader;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->fileDeleter = m::mock(FileDeleter::class);
        $this->fileUploader = m::mock(FileUploader::class);
        $this->validator = m::mock(PersonProfileImageValidator::class);
        $this->personStorageUpdater = m::mock(PersonStorageUpdater::class);

        $this->profileImageUploader = new PersonProfileImageUploader(
            $this->connection,
            $this->fileDeleter,
            $this->fileUploader,
            $this->validator,
            $this->personStorageUpdater
        );
    }

    /**
     * @test
     */
    public function upload()
    {
        $personId = 'person-uuid';
        $person = PersonModel::createFromArray([
            'id' => $personId,
            'image_id' => null,
            'image_thumb_1x_id' => null,
            'image_thumb_2x_id' => null,
        ]);
        $uploadedFile = m::mock(UploadedFile::class);

        $fileId = 'new-file-uuid';
        $file = FileModel::createFromArray([
            'id' => $fileId,
        ]);

        $this->createValidatorExpectation([['file' => $uploadedFile]]);
        $this->createConnectionBeginTransactionExpectation();
        $this->createFileUploaderExpectation([$person, $uploadedFile, 'profile_image'], $file);
        $this->createPersonStorageUpdaterExpectation(
            [
                $personId,
                [
                    'image_id' => $fileId,
                    'image_thumb_1x_id' => null,
                    'image_thumb_2x_id' => null,
                ],
            ]
        );
        $this->createConnectionCommitExpectation();

        $result = $this->profileImageUploader->upload($person, $uploadedFile);
        $this->assertInstanceOf(FileModel::class, $result);
    }

    /**
     * @test
     */
    public function uploadReplacesExistingImages()
    {
        $personId = 'person-uuid';
        $previousImageId = 'image-uuid';
        $previousThumb1xId = 'thumb1-uuid';
        $previousThumb2xId = 'thumb2-uuid';
        $person = PersonModel::createFromArray([
            'id' => $personId,
            'image_id' => $previousImageId,
            'image_thumb_1x_id' => $previousThumb1xId,
            'image_thumb_2x_id' => $previousThumb2xId,
        ]);
        $uploadedFile = m::mock(UploadedFile::class);

        $fileId = 'new-file-uuid';
        $file = FileModel::createFromArray([
            'id' => $fileId,
        ]);

        $this->createValidatorExpectation([['file' => $uploadedFile]]);
        $this->createConnectionBeginTransactionExpectation();
        $this->createFileUploaderExpectation([$person, $uploadedFile, 'profile_image'], $file);
        $this->createPersonStorageUpdaterExpectation(
            [
                $personId,
                [
                    'image_id' => $fileId,
                    'image_thumb_1x_id' => null,
                    'image_thumb_2x_id' => null,
                ],
            ]
        );
        $this->createFileDeleterExpectation([[$previousImageId, $previousThumb1xId, $previousThumb2xId]]);
        $this->createConnectionCommitExpectation();

        $result = $this->profileImageUploader->upload($person, $uploadedFile);
        $this->assertInstanceOf(FileModel::class, $result);
    }

    private function createValidatorExpectation($args)
    {
        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }

    private function createConnectionBeginTransactionExpectation()
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createFileUploaderExpectation($args, $result)
    {
        $this->fileUploader
            ->shouldReceive('upload')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createPersonStorageUpdaterExpectation($args)
    {
        $this->personStorageUpdater
            ->shouldReceive('update')
            ->once()
            ->with(...$args);
    }

    private function createFileDeleterExpectation($args)
    {
        $this->fileDeleter
            ->shouldReceive('markForDeletion')
            ->once()
            ->with(...$args);
    }

    private function createConnectionCommitExpectation()
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }
}
