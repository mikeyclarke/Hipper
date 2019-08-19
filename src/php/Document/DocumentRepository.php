<?php
declare(strict_types=1);

namespace Hipper\Document;

use Doctrine\DBAL\Connection;

class DocumentRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function findById(string $id, string $organizationId): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from('document')
            ->andWhere('id = :id')
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'id' => $id,
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetch();

        if (empty($result)) {
            return null;
        }

        return $result;
    }
}
