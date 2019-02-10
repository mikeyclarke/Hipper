<?php
declare(strict_types=1);

namespace Lithos\Team;

use Doctrine\DBAL\Connection;

class TeamRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function existsWithName(string $organizationId, string $name): bool
    {
        $stmt = $this->connection->executeQuery(
            'SELECT EXISTS (
                SELECT 1 FROM team WHERE organization_id = ? AND LOWER(name) = LOWER(?)
            )',
            [$organizationId, $name]
        );
        return (bool) $stmt->fetchColumn();
    }
}
