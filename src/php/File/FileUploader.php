<?php

declare(strict_types=1);

namespace Hipper\File;

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
    private FileInserter $fileInserter;
    private FileTypeGuesser $fileTypeGuesser;
    private FileWriter $fileWriter;
    private IdGenerator $idGenerator;
    private ImageResourceFactory $imageResourceFactory;
    private StoragePathGenerator $storagePathGenerator;

    public function __construct(
        FileInserter $fileInserter,
        FileTypeGuesser $fileTypeGuesser,
        FileWriter $fileWriter,
        IdGenerator $idGenerator,
        ImageResourceFactory $imageResourceFactory,
        StoragePathGenerator $storagePathGenerator,
    ) {
        $this->fileInserter = $fileInserter;
        $this->fileTypeGuesser = $fileTypeGuesser;
        $this->fileWriter = $fileWriter;
        $this->idGenerator = $idGenerator;
        $this->imageResourceFactory = $imageResourceFactory;
        $this->storagePathGenerator = $storagePathGenerator;
    }

    public function upload(PersonModel $person, UploadedFile $uploadedFile, string $usage): FileModel
    {
        $extension = $uploadedFile->guessExtension();
        if (null === $extension) {
            // BAD
        }

        $id = $this->idGenerator->generate();
        $storagePath = $this->storagePathGenerator->generate($usage, $extension);
        $mimeType = $uploadedFile->getMimeType();
        $fileType = $this->fileTypeGuesser->guessFromMimeType($mimeType);

        $contentHash = md5_file($uploadedFile->getPathname());
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
