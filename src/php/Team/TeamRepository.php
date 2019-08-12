<?php
declare(strict_types=1);

namespace Hipper\Team;

use Doctrine\DBAL\Connection;

class TeamRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function findByUrlId(string $organizationId, string $urlId): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('team')
            ->andWhere('organization_id = :organization_id')
            ->andWhere('url_id = :url_id');

        $qb->setParameters([
            'organization_id' => $organizationId,
            'url_id' => $urlId,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
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

    public function existsWithMappingForPerson(string $teamId, string $personId): bool
    {
        $stmt = $this->connection->executeQuery(
            'SELECT EXISTS (
                SELECT 1 FROM person_to_team_map WHERE team_id = ? AND person_id = ?
            )',
            [$teamId, $personId]
        );
        return (bool) $stmt->fetchColumn();
    }
}
