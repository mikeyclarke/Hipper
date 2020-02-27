<?php
declare(strict_types=1);

namespace Hipper\Organization\Storage;

use Doctrine\DBAL\Connection;

class OrganizationUpdater
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function update(string $id, array $properties): void
    {
        $this->connection->update(
            'organization',
            $properties,
            [
                'id' => $id,
            ]
        );
    }
}
