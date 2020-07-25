<?php
declare(strict_types=1);

namespace Hipper\Project;

use Doctrine\DBAL\Connection;
use InvalidArgumentException;

class ProjectRepository
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
            ->from('project')
            ->where('organization_id = :organization_id')
            ->orderBy($sortBy, $orderBy);

        $qb->setParameter('organization_id', $organizationId);

        $stmt = $qb->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    public function findById(string $id, string $organizationId): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::DEFAULT_FIELDS)
            ->from('project')
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

    public function findByUrlSlug(string $urlSlug, string $organizationId): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::DEFAULT_FIELDS)
            ->from('project')
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
            ->from('project')
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
            'project.id',
            'project.name',
            'project.description',
            'project.url_slug',
            'project.created',
        ];

        $qb->select($fields)
            ->from('person_to_project_map', 'map')
            ->leftJoin('map', 'project', 'project', 'project.id = map.project_id')
            ->andWhere('map.person_id = :person_id')
            ->andWhere('project.organization_id = :organization_id');

        $qb->setParameters([
            'person_id' => $personId,
            'organization_id' => $organizationId,
        ]);

        $qb->orderBy('project.name', 'ASC');

        $stmt = $qb->execute();
        return $stmt->fetchAll();
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
