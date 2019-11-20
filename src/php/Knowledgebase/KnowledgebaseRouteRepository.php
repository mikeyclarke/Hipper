<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class KnowledgebaseRouteRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function findRouteWithRouteAndUrlId(
        string $organizationId,
        string $knowledgebaseId,
        string $route,
        string $urlId
    ): ?array {
        $qb = $this->connection->createQueryBuilder();
        $this->createQuery($qb, $organizationId, $knowledgebaseId);

        $qb->andWhere('route = :route');
        $qb->andWhere('url_id = :url_id');

        $qb->setParameter('route', $route);
        $qb->setParameter('url_id', $urlId);

        $stmt = $qb->execute();
        $result = $stmt->fetch();
        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function findCanonicalRouteByRoute(string $organizationId, string $knowledgebaseId, string $route): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $this->createQuery($qb, $organizationId, $knowledgebaseId);

        $qb->andWhere('route = :route');
        $qb->andWhere('is_canonical IS TRUE');

        $qb->setParameter('route', $route);

        $stmt = $qb->execute();
        $result = $stmt->fetch();
        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function findCanonicalRouteByUrlId(string $organizationId, string $knowledgebaseId, string $urlId): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $this->createQuery($qb, $organizationId, $knowledgebaseId);

        $qb->andWhere('url_id = :url_id');
        $qb->andWhere('is_canonical IS TRUE');

        $qb->setParameter('url_id', $urlId);

        $stmt = $qb->execute();
        $result = $stmt->fetch();
        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function findCanonicalRouteForSection(
        string $organizationId,
        string $knowledgebaseId,
        string $sectionId
    ): ?array {
        $qb = $this->connection->createQueryBuilder();
        $this->createQuery($qb, $organizationId, $knowledgebaseId);

        $qb->andWhere('section_id = :section_id');
        $qb->andWhere('is_canonical IS TRUE');

        $qb->setParameter('section_id', $sectionId);

        $stmt = $qb->execute();
        $result = $stmt->fetch();
        if (false === $result) {
            return null;
        }

        return $result;
    }

    private function createQuery(QueryBuilder $qb, string $organizationId, string $knowledgebaseId): void
    {
        $qb->select('*')
            ->from('knowledgebase_route')
            ->andWhere('knowledgebase_id = :knowledgebase_id')
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
    }
}
