<?php
declare(strict_types=1);

namespace Hipper\Project;

use Doctrine\DBAL\Connection;

class ProjectInserter
{
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
        string $urlId,
        string $knowledgebaseId,
        string $organizationId
    ): array {
        $sql = "INSERT INTO project (id, name, description, url_id, knowledgebase_id, organization_id) " .
            "VALUES (:id, :name, :description, :url_id, :knowledgebase_id, :organization_id) RETURNING *";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->bindValue('name', $name);
        $stmt->bindValue('description', $description);
        $stmt->bindValue('url_id', $urlId);
        $stmt->bindValue('knowledgebase_id', $knowledgebaseId);
        $stmt->bindValue('organization_id', $organizationId);
        $stmt->execute();
        return $stmt->fetch();
    }
}
