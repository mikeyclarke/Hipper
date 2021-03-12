<?php

declare(strict_types=1);

namespace Hipper\Person;

use Doctrine\DBAL\Connection;
use Hipper\File\FileModel;
use Hipper\File\FileUploader;
use Hipper\File\Storage\FileDeleter;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonProfileImageValidator;
use Hipper\Person\Storage\PersonUpdater as PersonStorageUpdater;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PersonProfileImageUploader
{
    private Connection $connection;
    private FileDeleter $fileDeleter;
    private FileUploader $fileUploader;
    private PersonProfileImageValidator $validator;
    private PersonStorageUpdater $personStorageUpdater;

    public function __construct(
        Connection $connection,
        FileDeleter $fileDeleter,
        FileUploader $fileUploader,
        PersonProfileImageValidator $validator,
        PersonStorageUpdater $personStorageUpdater
    ) {
        $this->connection = $connection;
        $this->fileDeleter = $fileDeleter;
        $this->fileUploader = $fileUploader;
        $this->validator = $validator;
        $this->personStorageUpdater = $personStorageUpdater;
    }

    public function upload(PersonModel $person, UploadedFile $uploadedFile): FileModel
    {
        $this->validator->validate(['file' => $uploadedFile]);

        $this->connection->beginTransaction();
        try {
            $file = $this->fileUploader->upload($person, $uploadedFile, 'profile_image');

            $this->deleteExistingImages($person);
            $this->personStorageUpdater->update($person->getId(), [
                'image_id' => $file->getId(),
                'image_thumb_1x_id' => null,
                'image_thumb_2x_id' => null,
            ]);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        return $file;
    }

    private function deleteExistingImages(PersonModel $person): void
    {
        $files = [];

        if (null !== $person->getImageId()) {
            $files[] = $person->getImageId();
        }

        if (null !== $person->getImageThumb1xId()) {
            $files[] = $person->getImageThumb1xId();
        }

        if (null !== $person->getImageThumb2xId()) {
            $files[] = $person->getImageThumb2xId();
        }

        if (!empty($files)) {
            $this->fileDeleter->deleteSelection($files);
        }
    }
}
