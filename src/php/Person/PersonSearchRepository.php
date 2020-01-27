<?php
declare(strict_types=1);

namespace Hipper\Person;

use Doctrine\DBAL\Connection;

class PersonSearchRepository
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function getResults(string $searchQuery, string $organizationId, int $limit, int $offset): array
    {
        $innerQuery = <<<SQL
    SELECT
        ts_rank(person.search_tokens, websearch_to_tsquery('simple', :search_query)) AS rank,
        person.name AS name,
        person.abbreviated_name AS abbreviated_name,
        person.email_address AS email_address,
        person.job_role_or_title AS job_role_or_title,
        person.created AS created
    FROM person
    WHERE person.search_tokens @@ websearch_to_tsquery('simple', :search_query)
    AND person.organization_id = :organization_id
    ORDER BY rank DESC, created DESC
    LIMIT :limit
    OFFSET :offset
SQL;
        $sql = $this->addOuterQuery($innerQuery);

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('search_query', $searchQuery);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->bindValue('limit', $limit);
        $stmt->bindValue('offset', $offset);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getResultsInTeam(
        string $searchQuery,
        string $organizationId,
        string $teamId,
        int $limit,
        int $offset
    ): array {
        $innerQuery = <<<SQL
    SELECT
        ts_rank(person.search_tokens, websearch_to_tsquery('simple', :search_query)) AS rank,
        person.name AS name,
        person.abbreviated_name AS abbreviated_name,
        person.email_address AS email_address,
        person.job_role_or_title AS job_role_or_title,
        person.created AS created
    FROM person_to_team_map map
    INNER JOIN person ON person.id = map.person_id
    WHERE map.team_id = :team_id
    AND person.search_tokens @@ websearch_to_tsquery('simple', :search_query)
    AND person.organization_id = :organization_id
    ORDER BY rank DESC, created DESC
    LIMIT :limit
    OFFSET :offset
SQL;
        $sql = $this->addOuterQuery($innerQuery);

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('search_query', $searchQuery);
        $stmt->bindValue('team_id', $teamId);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->bindValue('limit', $limit);
        $stmt->bindValue('offset', $offset);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getResultsInProject(
        string $searchQuery,
        string $organizationId,
        string $projectId,
        int $limit,
        int $offset
    ): array {
        $innerQuery = <<<SQL
    SELECT
        ts_rank(person.search_tokens, websearch_to_tsquery('simple', :search_query)) AS rank,
        person.name AS name,
        person.abbreviated_name AS abbreviated_name,
        person.email_address AS email_address,
        person.job_role_or_title AS job_role_or_title,
        person.created AS created
    FROM person_to_project_map map
    INNER JOIN person ON person.id = map.person_id
    WHERE map.project_id = :project_id
    AND person.search_tokens @@ websearch_to_tsquery('simple', :search_query)
    AND person.organization_id = :organization_id
    ORDER BY rank DESC, created DESC
    LIMIT :limit
    OFFSET :offset
SQL;
        $sql = $this->addOuterQuery($innerQuery);

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('search_query', $searchQuery);
        $stmt->bindValue('project_id', $projectId);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->bindValue('limit', $limit);
        $stmt->bindValue('offset', $offset);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function addOuterQuery(string $innerQuery): string
    {
        $sql = <<<SQL
SELECT
    name,
    abbreviated_name,
    email_address,
    job_role_or_title,
    created,
    ts_headline(
        'simple',
        job_role_or_title,
        websearch_to_tsquery('simple', :search_query),
        'StartSel = %ts-mark%, StopSel = %/ts-mark%, HighlightAll=TRUE'
    ) AS job_role_or_title_snippet
FROM (
$innerQuery
) AS foo;
SQL;
        return $sql;
    }
}
