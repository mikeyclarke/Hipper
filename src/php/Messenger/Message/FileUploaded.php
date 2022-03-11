<?php

declare(strict_types=1);

namespace Hipper\Messenger\Message;

use Hipper\Messenger\Message\LowPriorityAsyncMessageInterface;

class FileUploaded implements LowPriorityAsyncMessageInterface
{
    public function __construct(
        private string $fileId,
        private string $usage,
    ) {}

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function getUsage(): string
    {
        return $this->usage;
    }
}
