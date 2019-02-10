<?php
declare(strict_types=1);

namespace Lithos\Person;

use Doctrine\DBAL\Connection;

class PersonMetadataInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(string $id, string $personId)
    {
        $this->connection->insert(
            'person_metadata',
            [
                'id' => $id,
                'person_id' => $personId,
            ]
        );
    }
}
