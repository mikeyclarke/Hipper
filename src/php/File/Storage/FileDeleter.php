<?php

declare(strict_types=1);

namespace Hipper\File\Storage;

use Doctrine\DBAL\Connection;

class FileDeleter
{
    private Connection $connection;

    public function __construct(
        Connection $connection,
    ) {
        $this->connection = $connection;
    }

    public function delete(string $id): void
    {
        $this->connection->delete('file', ['id' => $id]);
    }
}
