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

    public function getResults(string $searchQuery, string $organizationId): array
    {
        $andWhereConditions = 'AND doc_search.organization_id = :organization_id';
        $sql = $this->buildQuery($andWhereConditions);

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('search_query', $searchQuery);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getResultsInKnowledgebase(
        string $searchQuery,
        string $organizationId,
        string $knowledgebaseId
    ): array {
        $andWhereConditions =
            'AND doc_search.organization_id = :organization_id AND doc_search.knowledgebase_id = :knowledgebase_id';
        $sql = $this->buildQuery($andWhereConditions);

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('search_query', $searchQuery);
        $stmt->bindValue('knowledgebase_id', $knowledgebaseId);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function buildQuery(string $andWhereConditions): string
    {
        $sql = <<<SQL
SELECT
    id,
    name,
    description,
    deduced_description,
    knowledgebase_id,
    updated,
    ts_headline(
        description,
        websearch_to_tsquery(:search_query),
        'StartSel = <mark>, StopSel = </mark>, MaxFragments=2, FragmentDelimiter=" … "'
    ) AS description_snippet,
    ts_headline(
        content_plain,
        websearch_to_tsquery(:search_query),
        'StartSel = <mark>, StopSel = </mark>, MaxFragments=2, FragmentDelimiter=" … "'
    ) AS content_snippet,
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
        section.id AS id,
        section.name AS name,
        section.description AS description,
        '' AS deduced_description,
        section.updated AS updated,
        section.knowledgebase_id AS knowledgebase_id,
        section.organization_id AS organization_id,
        '' AS content_plain,
        section.search_tokens AS tokens,
        kb_route.route AS route,
        kb_route.url_id AS url_id,
        'section' AS entry_type
    FROM section section
    INNER JOIN knowledgebase_route kb_route ON kb_route.section_id = section.id AND kb_route.is_canonical IS TRUE
) doc_search
WHERE doc_search.tokens @@ websearch_to_tsquery(:search_query)
$andWhereConditions
ORDER BY ts_rank(doc_search.tokens, websearch_to_tsquery(:search_query), 1)
SQL;
        return $sql;
    }
}
