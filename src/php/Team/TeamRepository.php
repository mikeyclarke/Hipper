<?php
declare(strict_types=1);

namespace Hipper\Team;

use Doctrine\DBAL\Connection;
use InvalidArgumentException;

class TeamRepository
{
    private const ALLOWED_SORT_COLUMNS = ['created', 'name'];
    private const ALLOWED_ORDERINGS = ['ASC', 'DESC'];
    private const DEFAULT_FIELDS = [
        'id',
        'name',
        'description',
        'url_slug',
        'knowledgebase_id',
        'organization_id',
        'created',
        'updated',
    ];

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

        $qb->select(self::DEFAULT_FIELDS)
            ->from('team')
            ->where('organization_id = :organization_id')
            ->orderBy($sortBy, $orderBy);

        $qb->setParameter('organization_id', $organizationId);

        $stmt = $qb->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    public function findById(string $organizationId, string $id): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::DEFAULT_FIELDS)
            ->from('team')
            ->andWhere('organization_id = :organization_id')
            ->andWhere('id = :id');

        $qb->setParameters([
            'organization_id' => $organizationId,
            'id' => $id,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function findByUrlSlug(string $organizationId, string $urlSlug): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::DEFAULT_FIELDS)
            ->from('team')
            ->andWhere('organization_id = :organization_id')
            ->andWhere('url_slug = :url_slug');

        $qb->setParameters([
            'organization_id' => $organizationId,
            'url_slug' => $urlSlug,
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

        $qb->select(self::DEFAULT_FIELDS)
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

    public function getAllWithMappingForPerson(string $personId, string $organizationId): array
    {
        $qb = $this->connection->createQueryBuilder();

        $fields = [
            'team.id',
            'team.name',
            'team.description',
            'team.url_slug',
            'team.created',
        ];

        $qb->select($fields)
            ->from('person_to_team_map', 'map')
            ->leftJoin('map', 'team', 'team', 'team.id = map.team_id')
            ->andWhere('map.person_id = :person_id')
            ->andWhere('team.organization_id = :organization_id');

        $qb->setParameters([
            'person_id' => $personId,
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        return $stmt->fetchAll();
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
