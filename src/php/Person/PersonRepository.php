<?php
declare(strict_types=1);

namespace Hipper\Person;

use Doctrine\DBAL\Connection;

class PersonRepository
{
    private const DEFAULT_FIELDS = [
        'abbreviated_name',
        'bio',
        'created',
        'email_address',
        'email_address_verified',
        'id',
        'job_role_or_title',
        'name',
        'onboarding_completed',
        'organization_id',
        'updated',
        'url_id',
        'username',
    ];

    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
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
