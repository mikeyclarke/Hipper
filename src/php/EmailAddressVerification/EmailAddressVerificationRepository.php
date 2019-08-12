<?php
declare(strict_types=1);

namespace Hipper\EmailAddressVerification;

use Doctrine\DBAL\Connection;

class EmailAddressVerificationRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function get(string $personId, string $verificationPhrase): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('id')
            ->from('email_address_verification')
            ->andWhere('person_id = :personId')
            ->andWhere('verification_phrase = :verificationPhrase')
            ->andWhere('expires > CURRENT_TIMESTAMP');

        $qb->setParameters([
            'personId' => $personId,
            'verificationPhrase' => $verificationPhrase,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if ($result === false) {
            return null;
        }

        return $result;
    }
}
