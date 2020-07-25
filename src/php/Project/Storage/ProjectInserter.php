<?php
declare(strict_types=1);

namespace Hipper\Project\Storage;

use Doctrine\DBAL\Connection;

class ProjectInserter
{
    private const FIELDS_TO_RETURN = [
        'id',
        'name',
        'description',
        'url_slug',
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
        string $description,
        string $urlSlug,
        string $knowledgebaseId,
        string $organizationId
    ): array {
        $fieldsToReturn = implode(', ', self::FIELDS_TO_RETURN);
        $sql = "INSERT INTO project (id, name, description, url_slug, knowledgebase_id, organization_id) " .
            "VALUES (:id, :name, :description, :url_slug, :knowledgebase_id, :organization_id) " .
            "RETURNING {$fieldsToReturn}";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->bindValue('name', $name);
        $stmt->bindValue('description', $description);
        $stmt->bindValue('url_slug', $urlSlug);
        $stmt->bindValue('knowledgebase_id', $knowledgebaseId);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->execute();
        return $stmt->fetch();
    }
}
