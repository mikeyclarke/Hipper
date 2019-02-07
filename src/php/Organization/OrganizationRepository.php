<?php
declare(strict_types=1);

namespace Lithos\Organization;

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
}
