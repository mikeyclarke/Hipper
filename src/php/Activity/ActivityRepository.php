<?php
declare(strict_types=1);

namespace Hipper\Activity;

use Doctrine\DBAL\Connection;

class ActivityRepository
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function getActivityRelevantToUser(string $organizationId, int $limit = 10): array
    {
        $qb = $this->connection->createQueryBuilder();

        $fields = [
            'activity.id',
            'activity.type',
            'activity.storage',
            'activity.created',
            'activity.actor_id',
            'person.name AS actor_name',
            'person.abbreviated_name AS actor_abbreviated_name',
            'person.url_id AS actor_url_id',
            'person.username AS actor_username',
        ];

        $qb->select($fields)
            ->from('activity')
            ->innerJoin('activity', 'person', 'person', 'person.id = activity.actor_id')
            ->where('activity.organization_id = :organization_id')
            ->orderBy('activity.created', 'DESC')
            ->setMaxResults($limit);

        $qb->setParameters([
            'organization_id' => $organizationId,
        ]);

        $stmt = $qb->execute();
        return $stmt->fetchAll();
    }
}
