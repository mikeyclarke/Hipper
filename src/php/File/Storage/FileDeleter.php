<?php

declare(strict_types=1);

namespace Hipper\File\Storage;

use Doctrine\DBAL\Connection;

class FileDeleter
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function deleteSelection(array $ids): void
    {
        $this->connection->executeUpdate('DELETE FROM file WHERE id IN (?)', [$ids], [Connection::PARAM_STR_ARRAY]);
    }
}
