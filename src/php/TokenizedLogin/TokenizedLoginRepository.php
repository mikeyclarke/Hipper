<?php
declare(strict_types=1);

namespace Hipper\TokenizedLogin;

use Doctrine\DBAL\Connection;

class TokenizedLoginRepository
{
    private const DEFAULT_FIELDS = [
        'person_id',
    ];

    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function get(string $token): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::DEFAULT_FIELDS)
            ->from('tokenized_login')
            ->andWhere('token = :token')
            ->andWhere('expires > CURRENT_TIMESTAMP');

        $qb->setParameters([
            'token' => $token,
        ]);

        $statementResult = $qb->execute();
        $result = $statementResult->fetchAssociative();

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
