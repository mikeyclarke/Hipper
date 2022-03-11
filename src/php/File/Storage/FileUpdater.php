<?php

declare(strict_types=1);

namespace Hipper\File\Storage;

use Doctrine\DBAL\Connection;

class FileUpdater
{
    private Connection $connection;

    public function __construct(
        Connection $connection,
    ) {
        $this->connection = $connection;
    }

    public function update(string $fileId, array $properties): void
    {
        $this->connection->update(
            'file',
            $properties,
            [
                'id' => $fileId,
            ]
        );
    }

    public function bulkUpdate(array $properties, array $criteria): void
    {
        $this->connection->update('file', $properties, $criteria);
    }
}
