<?php
declare(strict_types=1);

namespace Lithos\Person;

use Doctrine\DBAL\Connection;
use PDO;

class PersonInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert($id, $name, $emailAddress, $password, $organizationId, $emailAddressVerified): ?array
    {
        $args = [
            $id => PDO::PARAM_STR,
            $name => PDO::PARAM_STR,
            $emailAddress => PDO::PARAM_STR,
            $password => PDO::PARAM_STR,
            $organizationId => PDO::PARAM_STR,
            $emailAddressVerified => PDO::PARAM_BOOL,
        ];

        $stmt = $this->connection->executeQuery(
            "INSERT INTO person (id, name, email_address, password, organization_id, email_address_verified) " .
            "VALUES (?, ?, ?, ?, ?, ?) RETURNING *",
            array_keys($args),
            array_values($args)
        );
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }
}
