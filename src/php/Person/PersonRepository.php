<?php
declare(strict_types=1);

namespace Hipper\Person;

use Doctrine\DBAL\Connection;
use InvalidArgumentException;

class PersonRepository
{
    private const ALLOWED_SORT_COLUMNS = ['created', 'name'];
    private const ALLOWED_ORDERINGS = ['ASC', 'DESC'];
    private const DEFAULT_FIELDS = [
        'person.abbreviated_name',
        'person.bio',
        'person.created',
        'person.email_address',
        'person.email_address_verified',
        'person.id',
        'person.job_role_or_title',
        'person.name',
        'person.onboarding_completed',
        'person.organization_id',
        'person.updated',
        'person.url_id',
        'person.username',
    ];

    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function getAll(
        string $organizationId,
        string $sortBy = 'created',
        string $orderBy = 'DESC',
        array $additionalFields = []
    ): array {
        if (!in_array($sortBy, self::ALLOWED_SORT_COLUMNS)) {
            throw new InvalidArgumentException('Unsupported `sortBy` column');
        }

        if (!in_array($orderBy, self::ALLOWED_ORDERINGS)) {
            throw new InvalidArgumentException('Unsupported `orderBy` value');
        }

        $qb = $this->connection->createQueryBuilder();

        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb->select($fields)
            ->from('person')
            ->where('organization_id = :organization_id')
            ->orderBy($sortBy, $orderBy);

        $qb->setParameter('organization_id', $organizationId);

        $stmt = $qb->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getAllInTeam(string $teamId, string $organizationId): array
    {
        $fields = self::DEFAULT_FIELDS;

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from('person_to_team_map', 'map')
            ->innerJoin('map', 'person', 'person', 'person.id = map.person_id')
            ->andWhere('map.team_id = :team_id')
            ->andWhere('person.organization_id = :organization_id');

        $qb->setParameters([
            'team_id' => $teamId,
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    public function findById(string $id, array $additionalFields = []): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb->select($fields)
            ->from('person')
            ->where('id = :id');

        $qb->setParameter('id', $id);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function findOneByUrlId(
        string $urlId,
        string $organizationId,
        array $additionalFields = []
    ): ?array {
        $qb = $this->connection->createQueryBuilder();

        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb->select($fields)
            ->from('person')
            ->andWhere('url_id = :url_id')
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'url_id' => $urlId,
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function findOneByEmailAddress(
        string $emailAddress,
        string $organizationId,
        array $additionalFields = []
    ): ?array {
        $qb = $this->connection->createQueryBuilder();

        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb->select($fields)
            ->from('person')
            ->andWhere('email_address = :email_address')
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'email_address' => $emailAddress,
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function getUsernamesLike(string $username, string $organizationId)
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('username')
            ->from('person')
            ->andWhere(
                $qb->expr()->like('username', ':username_like')
            )
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'username_like' => $username . '%',
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        return $stmt->fetchAll();
    }

    public function existsWithUsername(string $username, string $organizationId): bool
    {
        $stmt = $this->connection->executeQuery(
            'SELECT EXISTS (
                SELECT 1 FROM person WHERE username = ? AND organization_id = ?
            )',
            [$username, $organizationId]
        );
        return (bool) $stmt->fetchColumn();
    }

    public function existsWithEmailAddress(string $emailAddress): bool
    {
        $stmt = $this->connection->executeQuery(
            'SELECT EXISTS (
                SELECT 1 FROM person WHERE email_address = ?
            )',
            [$emailAddress]
        );
        return (bool) $stmt->fetchColumn();
    }
}
