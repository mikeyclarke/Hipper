<?php

declare(strict_types=1);

namespace Hipper\Messenger\MessageHandler;

use Hipper\File\FileModel;
use Hipper\File\FileReader;
use Hipper\File\FileRepository;
use Hipper\File\TempFileWriter;
use Hipper\Messenger\Message\ProfileImageUploaded;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProfileImageUploadedHandler implements MessageHandlerInterface
{
    public function __construct(
        private FileRepository $fileRepository,
        private FileReader $fileReader,
        private TempFileWriter $tempFileWriter,
        private string $fileBaseUrl,
    ) {}

    public function __invoke(ProfileImageUploaded $message): void
    {
        $image = $this->getImage($message);

        $tempPathname = $this->createTempFile($image);

        // Generate thumbs
        // Optimize thumbs
        // Upload thumbs
        // Update person
    }

    private function getImage(ProfileImageUploaded $message): FileModel
    {
        $result = $this->fileRepository->findById($message->getFileId());
        if (null === $result) {
            throw new \Exception('File not found.');
        }
        return FileModel::createFromArray($result);
    }

    private function createTempFile(FileModel $file): string
    {
        $url = sprintf('%s/%s', $this->fileBaseUrl, $file->getStoragePath());
        $fileContents = $this->fileReader->read($url);
        return $this->tempFileWriter->write(sprintf('profile_image_upload_processor_%s', $file->getId()), $fileContents);
    }

    private function clearTempFile(string $pathname): void
    {
        $this->tempFileWriter->remove($pathname);
    }
}
