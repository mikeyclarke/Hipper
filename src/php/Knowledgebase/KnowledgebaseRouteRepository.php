<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class KnowledgebaseRouteRepository
{
    private const DEFAULT_FIELDS = [
        'id',
        'url_id',
        'route',
        'is_canonical',
        'entity',
        'organization_id',
        'knowledgebase_id',
        'topic_id',
        'document_id',
    ];

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

        $qb->orderBy('is_canonical', 'DESC');

        $statementResult = $qb->execute();
        $result = $statementResult->fetchAssociative();
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

        $statementResult = $qb->execute();
        $result = $statementResult->fetchAssociative();
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

        $statementResult = $qb->execute();
        $result = $statementResult->fetchAssociative();
        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function findCanonicalRouteForDocument(
        string $organizationId,
        string $knowledgebaseId,
        string $documentId
    ): ?array {
        $qb = $this->connection->createQueryBuilder();
        $this->createQuery($qb, $organizationId, $knowledgebaseId);

        $qb->andWhere('document_id = :document_id');
        $qb->andWhere('is_canonical IS TRUE');

        $qb->setParameter('document_id', $documentId);

        $statementResult = $qb->execute();
        $result = $statementResult->fetchAssociative();
        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function findCanonicalRouteForTopic(
        string $organizationId,
        string $knowledgebaseId,
        string $topicId
    ): ?array {
        $qb = $this->connection->createQueryBuilder();
        $this->createQuery($qb, $organizationId, $knowledgebaseId);

        $qb->andWhere('topic_id = :topic_id');
        $qb->andWhere('is_canonical IS TRUE');

        $qb->setParameter('topic_id', $topicId);

        $statementResult = $qb->execute();
        $result = $statementResult->fetchAssociative();
        if (false === $result) {
            return null;
        }

        return $result;
    }

    private function createQuery(QueryBuilder $qb, string $organizationId, string $knowledgebaseId): void
    {
        $qb->select(self::DEFAULT_FIELDS)
            ->from('knowledgebase_route')
            ->andWhere('knowledgebase_id = :knowledgebase_id')
            ->andWhere('organization_id = :organization_id');

        $qb->setParameters([
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
    }
}
