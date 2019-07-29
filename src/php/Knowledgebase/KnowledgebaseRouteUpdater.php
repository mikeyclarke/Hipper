<?php
declare(strict_types=1);

namespace Lithos\Knowledgebase;

use Doctrine\DBAL\Connection;

class KnowledgebaseRouteUpdater
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function updatePreviousCanonicalRoutes(
        string $newRouteId,
        string $urlId,
        string $knowledgebaseId,
        string $organizationId
    ): void {
        $qb = $this->connection->createQueryBuilder();
        $qb->update('knowledgebase_route')
            ->set('is_canonical', 'false')
            ->andWhere('url_id = :url_id')
            ->andWhere('knowledgebase_id = :knowledgebase_id')
            ->andWhere('organization_id = :organization_id')
            ->andWhere('id != :new_route_id');

        $qb->setParameters([
            'url_id' => $urlId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
            'new_route_id' => $newRouteId,
        ]);
    }
}
