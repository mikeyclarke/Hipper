<?php
declare(strict_types=1);

namespace Hipper\Organization;

use Doctrine\DBAL\Connection;

class OrganizationRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function findById(string $id): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('organization')
            ->where('id = :id');

        $qb->setParameter('id', $id);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function findBySubdomain(string $subdomain): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('organization')
            ->where('subdomain = :subdomain');

        $qb->setParameter('subdomain', $subdomain);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function existsWithSubdomain(string $subdomain): bool
    {
        $stmt = $this->connection->executeQuery(
            'SELECT EXISTS (
                SELECT 1 FROM organization WHERE subdomain = ?
            )',
            [$subdomain]
        );
        return (bool) $stmt->fetchColumn();
    }
}
