<?php
declare(strict_types=1);

namespace Lithos\Person;

use Doctrine\DBAL\Connection;

class PersonInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert($id, $name, $emailAddress, $password, $organizationId): ?array
    {
        $stmt = $this->connection->executeQuery(
            "INSERT INTO person (id, name, email_address, password, organization_id) " .
            "VALUES (?, ?, ?, ?, ?) RETURNING *",
            [$id, $name, $emailAddress, $password, $organizationId]
        );
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }
}
