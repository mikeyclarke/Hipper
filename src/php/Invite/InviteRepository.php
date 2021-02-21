<?php
declare(strict_types=1);

namespace Hipper\Invite;

use Doctrine\DBAL\Connection;

class InviteRepository
{
    private const DEFAULT_FIELDS = [
        'id',
        'email_address',
        'expires',
    ];

    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function findWithIds(array $ids): array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::DEFAULT_FIELDS)
            ->from('invite')
            ->where('id in (:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $statementResult = $qb->execute();
        $result = $statementResult->fetchAllAssociative();

        $indexed = [];
        foreach ($result as $row) {
            $indexed[$row['id']] = $row;
        }
        return $indexed;
    }

    public function find(string $id, string $organizationId, string $token): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::DEFAULT_FIELDS)
            ->from('invite')
            ->andWhere('id = :id')
            ->andWhere('organization_id = :organization_id')
            ->andWhere('token = :token');

        $qb->setParameters([
            'id' => $id,
            'organization_id' => $organizationId,
            'token' => $token,
        ]);

        $statementResult = $qb->execute();
        $result = $statementResult->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }

    public function existsWithEmailAddress(string $emailAddress): bool
    {
        $statementResult = $this->connection->executeQuery(
            'SELECT EXISTS (
                SELECT 1 FROM invite WHERE email_address = ?
            )',
            [$emailAddress]
        );
        return (bool) $statementResult->fetchOne();
    }
}
