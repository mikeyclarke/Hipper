<?php
declare(strict_types=1);

namespace Hipper\Document\Storage;

use Doctrine\DBAL\Connection;
use PDO;

class DocumentInserter
{
    private const DEFAULT_FIELDS = [
        'id',
        'name',
        'description',
        'deduced_description',
        'content',
        'url_slug',
        'url_id',
        'knowledgebase_id',
        'organization_id',
        'topic_id',
        'created_by',
        'last_updated_by',
        'created',
        'updated',
    ];

    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(
        string $id,
        string $name,
        string $urlSlug,
        string $urlId,
        string $knowledgebaseId,
        string $organizationId,
        string $createdBy,
        string $description = null,
        string $deducedDescription = null,
        string $content = null,
        string $contentPlain = null,
        string $topicId = null
    ): array {
        $fieldsToReturn = implode(', ', self::DEFAULT_FIELDS);

        $sql = <<<SQL
INSERT INTO document (
    id, name, description, deduced_description, content, content_plain, url_slug, url_id, knowledgebase_id,
    organization_id, topic_id, created_by, last_updated_by
) VALUES (
    :id, :name, :description, :deduced_description, :content, :content_plain, :url_slug, :url_id, :knowledgebase_id,
    :organization_id, :topic_id, :created_by, :last_updated_by
) RETURNING $fieldsToReturn
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id, PDO::PARAM_STR);
        $stmt->bindValue('name', $name, PDO::PARAM_STR);
        $stmt->bindValue('description', $description, PDO::PARAM_STR);
        $stmt->bindValue('deduced_description', $deducedDescription, PDO::PARAM_STR);
        $stmt->bindValue('content', $content, PDO::PARAM_STR);
        $stmt->bindValue('content_plain', $contentPlain, PDO::PARAM_STR);
        $stmt->bindValue('url_slug', $urlSlug, PDO::PARAM_STR);
        $stmt->bindValue('url_id', $urlId, PDO::PARAM_STR);
        $stmt->bindValue('knowledgebase_id', $knowledgebaseId, PDO::PARAM_STR);
        $stmt->bindValue('organization_id', $organizationId, PDO::PARAM_STR);
        $stmt->bindValue('topic_id', $topicId, PDO::PARAM_STR);
        $stmt->bindValue('created_by', $createdBy, PDO::PARAM_STR);
        $stmt->bindValue('last_updated_by', $createdBy, PDO::PARAM_STR);

        $statementResult = $stmt->execute();
        return $statementResult->fetchAssociative();
    }
}
