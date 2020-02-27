<?php
declare(strict_types=1);

namespace Hipper\Organization\Storage;

use Doctrine\DBAL\Connection;

class OrganizationInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(string $id, string $name): array
    {
        $sql = "INSERT INTO organization (id, name) VALUES (:id, :name) RETURNING *";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->bindValue('name', $name);
        $stmt->execute();
        return $stmt->fetch();
    }
}
