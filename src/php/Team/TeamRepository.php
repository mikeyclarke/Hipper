<?php
declare(strict_types=1);

namespace Hipper\Team;

use Doctrine\DBAL\Connection;
use InvalidArgumentException;

class TeamRepository
{
    const ALLOWED_SORT_COLUMNS = ['created', 'name'];
    const ALLOWED_ORDERINGS = ['ASC', 'DESC'];

    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function getAll(string $organizationId, string $sortBy = 'created', string $orderBy = 'DESC'): array
    {
        if (!in_array($sortBy, self::ALLOWED_SORT_COLUMNS)) {
            throw new InvalidArgumentException('Unsupported `sortBy` column');
        }

        if (!in_array($orderBy, self::ALLOWED_ORDERINGS)) {
            throw new InvalidArgumentException('Unsupported `orderBy` value');
        }

        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('team')
            ->where('organization_id = :organization_id')
            ->orderBy($sortBy, $orderBy);

        $qb->setParameter('organization_id', $organizationId);

        $stmt = $qb->execute();
        $result = $stmt->fetchAll();

        return $result;
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

    public function findByKnowledgebaseId(string $knowledgebaseId, string $organizationId): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('team')
            ->andWhere('organization_id = :organization_id')
            ->andWhere('knowledgebase_id = :knowledgebase_id');

        $qb->setParameters([
            'organization_id' => $organizationId,
            'knowledgebase_id' => $knowledgebaseId,
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
