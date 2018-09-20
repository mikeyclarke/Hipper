<?php
declare(strict_types=1);

namespace Lithos\Person;

use Doctrine\DBAL\Connection;

class PersonUpdater
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function update(string $personId, array $properties): void
    {
        $this->connection->update(
            'person',
            $properties,
            [
                'id' => $personId,
            ]
        );
    }
}
