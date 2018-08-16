<?php
namespace hleo\EmailAddressVerification;

use Doctrine\DBAL\Connection;

class EmailAddressVerificationRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function get(string $personId, string $id, string $hash)
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('id')
            ->from('email_address_verification')
            ->andWhere('id = :id')
            ->andWhere('person_id = :personId')
            ->andWhere('hash = :hash')
            ->andWhere('expires > CURRENT_TIMESTAMP');

        $qb->setParameters([
            'personId' => $personId,
            'id' => $id,
            'hash' => $hash,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if ($result === false) {
            return null;
        }

        return $result;
    }
}
