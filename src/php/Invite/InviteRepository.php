<?php
declare(strict_types=1);

namespace Lithos\Invite;

use Doctrine\DBAL\Connection;

class InviteRepository
{
    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function findWithIds(array $ids): array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('invite')
            ->where('id in (:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $stmt = $qb->execute();
        $result = $stmt->fetchAll();

        $indexed = [];
        foreach ($result as $row) {
            $indexed[$row['id']] = $row;
        }
        return $indexed;
    }

    public function find(string $id, string $organizationId, string $token): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from('invite')
            ->andWhere('id = :id')
            ->andWhere('organization_id = :organization_id')
            ->andWhere('token = :token')
            ->andWhere('expires > CURRENT_TIMESTAMP');

        $qb->setParameters([
            'id' => $id,
            'organization_id' => $organizationId,
            'token' => $token,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();
        if (false === $result) {
            return null;
        }
        return $result;
    }

    public function existsWithEmailAddress(string $emailAddress): bool
    {
        $stmt = $this->connection->executeQuery(
            'SELECT EXISTS (
                SELECT 1 FROM invite WHERE email_address = ?
            )',
            [$emailAddress]
        );
        return (bool) $stmt->fetchColumn();
    }
}
