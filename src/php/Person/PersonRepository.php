<?php
declare(strict_types=1);

namespace Lithos\Person;

use Doctrine\DBAL\Connection;

class PersonRepository
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

    public function findOneByEmailAddress(string $emailAddress): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('person')
            ->where('email_address = :email_address');

        $qb->setParameter('email_address', $emailAddress);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }
}
