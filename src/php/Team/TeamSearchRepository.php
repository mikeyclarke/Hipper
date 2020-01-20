<?php
declare(strict_types=1);

namespace Hipper\Team;

use Doctrine\DBAL\Connection;

class TeamSearchRepository
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function getResults(string $searchQuery, string $organizationId): array
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
    FROM team
    WHERE search_tokens @@ websearch_to_tsquery('english', :search_query)
    AND organization_id = :organization_id
    ORDER BY ts_rank(search_tokens, websearch_to_tsquery('english', :search_query), 1) DESC, created DESC
) AS foo;
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('search_query', $searchQuery);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
