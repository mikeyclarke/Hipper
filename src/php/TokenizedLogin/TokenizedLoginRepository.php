<?php
declare(strict_types=1);

namespace Hipper\TokenizedLogin;

use Doctrine\DBAL\Connection;

class TokenizedLoginRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function get(string $token): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('tokenized_login')
            ->andWhere('token = :token')
            ->andWhere('expires > CURRENT_TIMESTAMP');

        $qb->setParameters([
            'token' => $token,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if ($result === false) {
            return null;
        }

        return $result;
    }

    public function deleteAllForPerson(string $personId): void
    {
        $this->connection->delete(
            'tokenized_login',
            [
                'person_id' => $personId,
            ]
        );
    }
}
