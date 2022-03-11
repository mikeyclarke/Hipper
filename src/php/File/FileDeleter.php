<?php

declare(strict_types=1);

namespace Hipper\File;

use Hipper\File\FileModel;
use Hipper\File\FileWriter;
use Hipper\File\Storage\FileDeleter as FileStorageDeleter;
use Hipper\File\Storage\FileUpdater as FileStorageUpdater;
use Hipper\Messenger\MessageBus;
use Hipper\Messenger\Message\FilesMarkedForDeletion;

class FileDeleter
{
    private FileStorageDeleter $fileStorageDeleter;
    private FileStorageUpdater $fileStorageUpdater;
    private FileWriter $fileWriter;
    private MessageBus $messageBus;

    public function __construct(
        FileStorageDeleter $fileStorageDeleter,
        FileStorageUpdater $fileStorageUpdater,
        FileWriter $fileWriter,
        MessageBus $messageBus,
    ) {
        $this->fileStorageDeleter = $fileStorageDeleter;
        $this->fileStorageUpdater = $fileStorageUpdater;
        $this->fileWriter = $fileWriter;
        $this->messageBus = $messageBus;
    }

    public function markForDeletion(array $fileIds): void
    {
        $this->fileStorageUpdater->bulkUpdate(
            ['marked_for_deletion' => true],
            ['id' => $fileIds]
        );

        $this->messageBus->dispatch(new FilesMarkedForDeletion($fileIds));
    }

    public function deleteFile(FileModel $file): void
    {
        $this->fileWriter->delete($file->getStoragePath());
        $this->fileStorageDeleter->delete($file->getId());
    }
}
