<?php

declare(strict_types=1);

namespace Hipper\Messenger\Message;

use Hipper\Messenger\Message\LowPriorityAsyncMessageInterface;

class ProfileImageUploaded implements LowPriorityAsyncMessageInterface
{
    public function __construct(
        private string $personId,
        private string $fileId,
    ) {}

    public function getPersonId(): string
    {
        return $this->personId;
    }

    public function getFileId(): string
    {
        return $this->fileId;
    }
}
