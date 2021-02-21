<?php
declare(strict_types=1);

namespace Hipper\Activity;

use Doctrine\DBAL\Connection;

class ActivityRepository
{
    private const FIELDS = [
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

    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function getActivityRelevantToUser(string $organizationId, int $limit = 10): array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::FIELDS)
            ->from('activity')
            ->innerJoin('activity', 'person', 'person', 'person.id = activity.actor_id')
            ->where('activity.organization_id = :organization_id')
            ->orderBy('activity.created', 'DESC')
            ->setMaxResults($limit);

        $qb->setParameters([
            'organization_id' => $organizationId,
        ]);

        $statementResult = $qb->execute();
        return $statementResult->fetchAllAssociative();
    }

    public function getPersonActivity(string $personId, string $organizationId, int $limit = 10): array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::FIELDS)
            ->from('activity')
            ->innerJoin('activity', 'person', 'person', 'person.id = activity.actor_id')
            ->andWhere('activity.actor_id = :actor_id')
            ->andWhere('activity.organization_id = :organization_id')
            ->orderBy('activity.created', 'DESC')
            ->setMaxResults($limit);

        $qb->setParameters([
            'organization_id' => $organizationId,
            'actor_id' => $personId,
        ]);

        $statementResult = $qb->execute();
        return $statementResult->fetchAllAssociative();
    }

    public function getTeamActivity(string $teamId, string $organizationId, int $limit = 10): array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::FIELDS)
            ->from('activity')
            ->innerJoin('activity', 'person', 'person', 'person.id = activity.actor_id')
            ->andWhere('activity.team_id = :team_id')
            ->andWhere('activity.organization_id = :organization_id')
            ->orderBy('activity.created', 'DESC')
            ->setMaxResults($limit);

        $qb->setParameters([
            'organization_id' => $organizationId,
            'team_id' => $teamId,
        ]);

        $statementResult = $qb->execute();
        return $statementResult->fetchAllAssociative();
    }

    public function getProjectActivity(string $projectId, string $organizationId, int $limit = 10): array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::FIELDS)
            ->from('activity')
            ->innerJoin('activity', 'person', 'person', 'person.id = activity.actor_id')
            ->andWhere('activity.project_id = :project_id')
            ->andWhere('activity.organization_id = :organization_id')
            ->orderBy('activity.created', 'DESC')
            ->setMaxResults($limit);

        $qb->setParameters([
            'organization_id' => $organizationId,
            'project_id' => $projectId,
        ]);

        $statementResult = $qb->execute();
        return $statementResult->fetchAllAssociative();
    }
}
