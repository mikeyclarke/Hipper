<?php
declare(strict_types=1);

namespace Hipper\Person;

use Doctrine\DBAL\Connection;

class PersonKnowledgebaseEntryViewRepository
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function getMostRecentlyViewedForPerson(string $personId, int $limit): array
    {
        $sql = <<<SQL
SELECT *
FROM (
    (
        SELECT *
        FROM (
            SELECT DISTINCT ON (doc.id)
                doc.id AS id,
                doc.name AS name,
                doc.description AS description,
                doc.deduced_description AS deduced_description,
                doc.updated AS updated,
                doc.topic_id AS parent_topic_id,
                doc.knowledgebase_id AS knowledgebase_id,
                doc.organization_id AS organization_id,
                kb_route.route AS route,
                kb_route.url_id AS url_id,
                'document' AS entry_type,
                entry_view.created AS created
            FROM person_knowledgebase_entry_view entry_view
            INNER JOIN document doc ON doc.id = entry_view.document_id
            INNER JOIN knowledgebase_route kb_route ON kb_route.document_id = doc.id AND kb_route.is_canonical IS TRUE
            WHERE entry_view.person_id = :person_id
            AND entry_view.document_id IS NOT NULL
            ORDER BY doc.id, entry_view.created DESC
        ) d ORDER BY created DESC
    )

    UNION

    (
        SELECT *
        FROM (
            SELECT DISTINCT ON (topic.id)
                topic.id AS id,
                topic.name AS name,
                topic.description AS description,
                '' AS deduced_description,
                topic.updated AS updated,
                topic.parent_topic_id AS parent_topic_id,
                topic.knowledgebase_id AS knowledgebase_id,
                topic.organization_id AS organization_id,
                kb_route.route AS route,
                kb_route.url_id AS url_id,
                'topic' AS entry_type,
                entry_view.created AS created
            FROM person_knowledgebase_entry_view entry_view
            INNER JOIN topic ON topic.id = entry_view.topic_id
            INNER JOIN knowledgebase_route kb_route ON kb_route.topic_id = topic.id AND kb_route.is_canonical IS TRUE
            WHERE entry_view.person_id = :person_id
            AND entry_view.topic_id IS NOT NULL
            ORDER BY topic.id, entry_view.created DESC
        ) t ORDER BY created DESC
    )
) recents
ORDER BY created DESC
LIMIT :limit;
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('person_id', $personId);
        $stmt->bindValue('limit', $limit);
        $statementResult = $stmt->execute();
        return $statementResult->fetchAllAssociative();
    }
}
