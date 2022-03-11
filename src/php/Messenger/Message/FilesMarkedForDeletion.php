<?php

declare(strict_types=1);

namespace Hipper\Messenger\Message;

use Hipper\Messenger\Message\LowPriorityAsyncMessageInterface;

class FilesMarkedForDeletion implements LowPriorityAsyncMessageInterface
{
    private array $fileIds;

    public function __construct(
        array $fileIds
    ) {
        $this->fileIds = $fileIds;
    }

    public function getFileIds(): array
    {
        return $this->fileIds;
    }
}
