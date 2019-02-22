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
}
