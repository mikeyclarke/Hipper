<?php

declare(strict_types=1);

namespace Hipper\Messenger\MessageHandler;

use Hipper\File\FileDeleter;
use Hipper\File\FileModel;
use Hipper\File\FileRepository;
use Hipper\Messenger\Message\FilesMarkedForDeletion;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class FilesMarkedForDeletionHandler implements MessageHandlerInterface
{
    private FileDeleter $fileDeleter;
    private FileRepository $fileRepository;

    public function __construct(
        FileDeleter $fileDeleter,
        FileRepository $fileRepository,
    ) {
        $this->fileDeleter = $fileDeleter;
        $this->fileRepository = $fileRepository;
    }

    public function __invoke(FilesMarkedForDeletion $message): void
    {
        $fileIds = $message->getFileIds();
        foreach ($fileIds as $id) {
            $result = $this->fileRepository->findById($id);
            if (null === $result) {
                continue;
            }

            $file = FileModel::createFromArray($result);
            $this->fileDeleter->deleteFile($file);
        }
    }
}
