<?php
declare(strict_types=1);

namespace Hipper\Project;

use Doctrine\DBAL\Connection;

class ProjectSearchRepository
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function getResults(string $searchQuery, string $organizationId, int $limit, int $offset): array
    {
        $sql = <<<SQL
SELECT
    name,
    description,
    ts_headline(
        'english',
        description,
        websearch_to_tsquery('english', :search_query),
        'StartSel = %ts-mark%, StopSel = %/ts-mark%, HighlightAll=TRUE'
    ) AS description_snippet,
    url_id,
    created
FROM (
    SELECT
        name,
        description,
        url_id,
        created
    FROM project
    WHERE search_tokens @@ websearch_to_tsquery('english', :search_query)
    AND organization_id = :organization_id
    ORDER BY ts_rank(search_tokens, websearch_to_tsquery('english', :search_query), 1) DESC, created DESC
    LIMIT :limit
    OFFSET :offset
) AS foo;
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('search_query', $searchQuery);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->bindValue('limit', $limit);
        $stmt->bindValue('offset', $offset);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
