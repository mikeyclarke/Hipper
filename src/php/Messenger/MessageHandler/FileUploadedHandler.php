<?php

declare(strict_types=1);

namespace Hipper\Messenger\MessageHandler;

use Doctrine\DBAL\Connection;
use Hipper\File\FileHash;
use Hipper\File\FileModel;
use Hipper\File\FileReader;
use Hipper\File\FileRepository;
use Hipper\File\FileWriter;
use Hipper\File\Processor\ProcessorInterface;
use Hipper\File\StoragePathGenerator;
use Hipper\File\Storage\FileUpdater;
use Hipper\File\TempFileWriter;
use Hipper\Messenger\Message\FileUploaded;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class FileUploadedHandler implements MessageHandlerInterface
{
    private array $processors;

    public function __construct(
        private Connection $connection,
        private FileHash $fileHash,
        private FileReader $fileReader,
        private FileRepository $fileRepository,
        private FileUpdater $fileUpdater,
        private FileWriter $fileWriter,
        private StoragePathGenerator $storagePathGenerator,
        private TempFileWriter $tempFileWriter,
        private string $fileBaseUrl,
    ) {}

    public function registerProcessor(ProcessorInterface $processor): void
    {
        $this->processors[] = $processor;
    }

    public function __invoke(FileUploaded $message): void
    {
        $file = $this->getFile($message);

        $tempPathname = $this->createTempFile($file);

        try {
            foreach ($this->processors as $processor) {
                if ($processor->canProcessFile($file)) {
                    $processor->process($file, $tempPathname);
                }
            }
        } catch (\Exception $e) {
            $this->clearTempFile($tempPathname);
            throw $e;
        }

        if ($this->fileHash->equals($tempPathname, $file->getContentHash())) {
            $this->clearTempFile($tempPathname);
            $this->fileUpdater->update($file->getId(), [
                'processing' => 'completed',
            ]);
            return;
        }

        $originalStoragePath = $file->getStoragePath();
        $extension = mb_substr($originalStoragePath, mb_strpos($originalStoragePath, '.') + 1);
        $storagePath = $this->storagePathGenerator->generate($file->getUsage(), $extension);
        $contentHash = $this->fileHash->get($tempPathname);

        $this->connection->beginTransaction();
        try {
            $this->fileUpdater->update($file->getId(), [
                'content_hash' => $contentHash,
                'storage_path' => $storagePath,
                'processing' => 'completed',
            ]);

            $this->fileWriter->write($storagePath, $this->fileReader->read($tempPathname));
            $this->fileWriter->delete($originalStoragePath);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        } finally {
            $this->clearTempFile($tempPathname);
        }
    }

    private function getFile(FileUploaded $message): FileModel
    {
        $result = $this->fileRepository->findById($message->getFileId(), ['content_hash']);
        if (null === $result) {
            throw new \Exception('File not found.');
        }
        return FileModel::createFromArray($result);
    }

    private function createTempFile(FileModel $file): string
    {
        $url = sprintf('%s%s', $this->fileBaseUrl, $file->getStoragePath());
        $fileContents = $this->fileReader->read($url);
        return $this->tempFileWriter->write(sprintf('file_upload_processor_%s', $file->getId()), $fileContents);
    }

    private function clearTempFile(string $pathname): void
    {
        $this->tempFileWriter->remove($pathname);
    }
}
