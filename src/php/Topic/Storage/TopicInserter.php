<?php
declare(strict_types=1);

namespace Hipper\Topic\Storage;

use Doctrine\DBAL\Connection;
use PDO;

class TopicInserter
{
    private const FIELDS_TO_RETURN = [
        'id',
        'name',
        'description',
        'url_slug',
        'url_id',
        'parent_topic_id',
        'knowledgebase_id',
        'organization_id',
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
        string $description = null,
        string $parentTopicId = null
    ): array {
        $fieldsToReturn = implode(', ', self::FIELDS_TO_RETURN);

        $sql = <<<SQL
INSERT INTO topic (
    id, name, description, url_slug, url_id, knowledgebase_id, organization_id, parent_topic_id
) VALUES (
    :id, :name, :description, :url_slug, :url_id, :knowledgebase_id, :organization_id, :parent_topic_id
) RETURNING $fieldsToReturn
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id, PDO::PARAM_STR);
        $stmt->bindValue('name', $name, PDO::PARAM_STR);
        $stmt->bindValue('description', $description, PDO::PARAM_STR);
        $stmt->bindValue('url_slug', $urlSlug, PDO::PARAM_STR);
        $stmt->bindValue('url_id', $urlId, PDO::PARAM_STR);
        $stmt->bindValue('knowledgebase_id', $knowledgebaseId, PDO::PARAM_STR);
        $stmt->bindValue('organization_id', $organizationId, PDO::PARAM_STR);
        $stmt->bindValue('parent_topic_id', $parentTopicId, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetch();
    }
}
