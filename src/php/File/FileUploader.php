<?php

declare(strict_types=1);

namespace Hipper\File;

use Hipper\File\Exception\UnguessableFileExtensionException;
use Hipper\File\FileHash;
use Hipper\File\FileModel;
use Hipper\File\FileTypeGuesser;
use Hipper\File\FileWriter;
use Hipper\File\StoragePathGenerator;
use Hipper\File\Storage\FileInserter;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Image\ImageResource\ImageResourceFactory;
use Hipper\Person\PersonModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function __construct(
        private FileHash $fileHash,
        private FileInserter $fileInserter,
        private FileTypeGuesser $fileTypeGuesser,
        private FileWriter $fileWriter,
        private IdGenerator $idGenerator,
        private ImageResourceFactory $imageResourceFactory,
        private StoragePathGenerator $storagePathGenerator,
    ) {}

    public function upload(PersonModel $person, UploadedFile $uploadedFile, string $usage): FileModel
    {
        $extension = $uploadedFile->guessExtension();
        if (null === $extension) {
            throw new UnguessableFileExtensionException();
        }

        $id = $this->idGenerator->generate();
        $storagePath = $this->storagePathGenerator->generate($usage, $extension);
        $mimeType = $uploadedFile->getMimeType();
        $fileType = $this->fileTypeGuesser->guessFromMimeType($mimeType);

        $contentHash = $this->fileHash->get($uploadedFile->getPathname());
        $bytes = $uploadedFile->getSize();
        $height = null;
        $width = null;

        if ($fileType === 'image') {
            $pathname = $uploadedFile->getPathname();
            list($height, $width) = $this->getImageDimensions($pathname, $mimeType);
        }

        $result = $this->fileInserter->insert(
            $id,
            $contentHash,
            $storagePath,
            $fileType,
            $mimeType,
            $usage,
            $bytes,
            $height,
            $width,
            $person->getOrganizationId(),
            $person->getId()
        );
        $file = FileModel::createFromArray($result);

        $this->fileWriter->write($file->getStoragePath(), $uploadedFile->getContent());

        return $file;
    }

    private function getImageDimensions(string $pathname, string $mimeType): array
    {
        $imageResource = $this->imageResourceFactory->create($pathname, $mimeType);

        return [
            $imageResource->getHeight(),
            $imageResource->getWidth(),
        ];
    }
}
