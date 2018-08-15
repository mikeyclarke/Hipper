<?php
namespace hleo\Organization;

use Doctrine\DBAL\Connection;

class OrganizationInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert($id, $name)
    {
        $stmt = $this->connection->executeQuery(
            "INSERT INTO organization (id, name) VALUES (?, ?) RETURNING *",
            [$id, $name]
        );
        return $stmt->fetch();
    }
}
