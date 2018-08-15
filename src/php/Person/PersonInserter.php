<?php
namespace hleo\Person;

use Doctrine\DBAL\Connection;

class PersonInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert($id, $name, $emailAddress, $password, $organizationId, $role = 'member')
    {
        $stmt = $this->connection->executeQuery(
            "INSERT INTO person (id, name, emailAddress, password, organizationId, role) VALUES (?, ?, ?, ?, ?, ?) RETURNING *",
            [$id, $name, $emailAddress, $password, $organizationId, $role]
        );
        return $stmt->fetch();
    }
}
