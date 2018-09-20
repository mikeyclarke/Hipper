<?php
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

    public function findOneByEmailAddress(string $emailAddress)
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('person')
            ->where('email_address = :email_address');

        $qb->setParameter('email_address', $emailAddress);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return;
        }

        return $result;
    }
}
