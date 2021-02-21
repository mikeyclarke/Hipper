<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Doctrine\DBAL\Connection;

class KnowledgebaseSearchRepository
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function getResults(string $searchQuery, string $organizationId, int $limit, int $offset): array
    {
        $andWhereConditions = 'AND doc_search.organization_id = :organization_id';
        $sql = $this->buildQuery($andWhereConditions);

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('search_query', $searchQuery);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->bindValue('limit', $limit);
        $stmt->bindValue('offset', $offset);
        $statementResult = $stmt->execute();
        return $statementResult->fetchAllAssociative();
    }

    public function getResultsInKnowledgebase(
        string $searchQuery,
        string $organizationId,
        string $knowledgebaseId,
        int $limit,
        int $offset
    ): array {
        $andWhereConditions =
            'AND doc_search.organization_id = :organization_id AND doc_search.knowledgebase_id = :knowledgebase_id';
        $sql = $this->buildQuery($andWhereConditions);

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('search_query', $searchQuery);
        $stmt->bindValue('knowledgebase_id', $knowledgebaseId);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->bindValue('limit', $limit);
        $stmt->bindValue('offset', $offset);
        $statementResult = $stmt->execute();
        return $statementResult->fetchAllAssociative();
    }

    private function buildQuery(string $andWhereConditions): string
    {
        $sql = <<<SQL
SELECT
    id,
    name,
    description,
    deduced_description,
    parent_topic_id,
    knowledgebase_id,
    updated,
    ts_headline(
        description,
        websearch_to_tsquery('english', :search_query),
        'StartSel = %ts-mark%, StopSel = %/ts-mark%, HighlightAll=TRUE'
    ) AS description_snippet,
    ts_headline(
        content_plain,
        websearch_to_tsquery('english', :search_query),
        'StartSel = %ts-mark%, StopSel = %/ts-mark%, MinWords=5, MaxWords=50, MaxFragments=1'
    ) AS content_snippet,
    entry_type,
    route,
    url_id
FROM (
    SELECT
        id,
        name,
        description,
        deduced_description,
        content_plain,
        parent_topic_id,
        knowledgebase_id,
        updated,
        entry_type,
        route,
        url_id
    FROM (
        SELECT
            doc.id AS id,
            doc.name AS name,
            doc.description AS description,
            doc.deduced_description AS deduced_description,
            doc.updated AS updated,
            doc.topic_id AS parent_topic_id,
            doc.knowledgebase_id AS knowledgebase_id,
            doc.organization_id AS organization_id,
            doc.content_plain AS content_plain,
            doc.search_tokens AS tokens,
            kb_route.route AS route,
            kb_route.url_id AS url_id,
            'document' AS entry_type
        FROM document doc
        INNER JOIN knowledgebase_route kb_route ON kb_route.document_id = doc.id AND kb_route.is_canonical IS TRUE

        UNION ALL

        SELECT
            topic.id AS id,
            topic.name AS name,
            topic.description AS description,
            '' AS deduced_description,
            topic.updated AS updated,
            topic.parent_topic_id AS parent_topic_id,
            topic.knowledgebase_id AS knowledgebase_id,
            topic.organization_id AS organization_id,
            '' AS content_plain,
            topic.search_tokens AS tokens,
            kb_route.route AS route,
            kb_route.url_id AS url_id,
            'topic' AS entry_type
        FROM topic topic
        INNER JOIN knowledgebase_route kb_route ON kb_route.topic_id = topic.id AND kb_route.is_canonical IS TRUE
    ) doc_search
    WHERE doc_search.tokens @@ websearch_to_tsquery('english', :search_query)
    $andWhereConditions
    ORDER BY ts_rank(doc_search.tokens, websearch_to_tsquery('english', :search_query), 1) DESC, doc_search.updated DESC
    LIMIT :limit
    OFFSET :offset
) AS foo
SQL;
        return $sql;
    }
}
