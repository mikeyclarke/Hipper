<?php
declare(strict_types=1);

namespace Hipper\Project;

use Doctrine\DBAL\Connection;

class ProjectRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function findByUrlId(string $urlId, string $organizationId): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('project')
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

    public function existsWithName(string $name, string $organizationId): bool
    {
        $stmt = $this->connection->executeQuery(
            'SELECT EXISTS (
                SELECT 1 FROM project WHERE organization_id = ? AND LOWER(name) = LOWER(?)
            )',
            [$organizationId, $name]
        );
        return (bool) $stmt->fetchColumn();
    }

    public function existsWithMappingForPerson(string $projectId, string $personId): bool
    {
        $stmt = $this->connection->executeQuery(
            'SELECT EXISTS (
                SELECT 1 FROM person_to_project_map WHERE project_id = ? AND person_id = ?
            )',
            [$projectId, $personId]
        );
        return (bool) $stmt->fetchColumn();
    }
}
