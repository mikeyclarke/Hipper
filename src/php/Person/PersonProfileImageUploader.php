<?php

declare(strict_types=1);

namespace Hipper\Person;

use Doctrine\DBAL\Connection;
use Hipper\File\FileModel;
use Hipper\File\FileUploader;
use Hipper\File\FileDeleter;
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
        PersonStorageUpdater $personStorageUpdater,
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

        $existingImages = $this->getExistingImages($person);

        $this->connection->beginTransaction();
        try {
            $file = $this->fileUploader->upload($person, $uploadedFile, 'profile_image');

            $this->personStorageUpdater->update($person->getId(), [
                'image_id' => $file->getId(),
                'image_thumb_1x_id' => null,
                'image_thumb_2x_id' => null,
            ]);

            if (!empty($existingImages)) {
                $this->fileDeleter->markForDeletion($existingImages);
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        return $file;
    }

    private function getExistingImages(PersonModel $person): array
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

        return $files;
    }
}
