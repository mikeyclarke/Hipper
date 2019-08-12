<?php
declare(strict_types=1);

namespace Hipper\Document;

use Doctrine\DBAL\Connection;
use PDO;

class DocumentInserter
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
        string $urlSlug,
        string $urlId,
        string $knowledgebaseId,
        string $organizationId,
        string $createdBy,
        string $description = null,
        string $deducedDescription = null,
        string $content = null,
        string $sectionId = null
    ): array {
        $sql = <<<SQL
INSERT INTO document (
    id, name, description, deduced_description, content, url_slug, url_id, knowledgebase_id,
    organization_id, section_id, created_by, last_updated_by
) VALUES (
    :id, :name, :description, :deduced_description, :content, :url_slug, :url_id, :knowledgebase_id,
    :organization_id, :section_id, :created_by, :last_updated_by
) RETURNING *
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id, PDO::PARAM_STR);
        $stmt->bindValue('name', $name, PDO::PARAM_STR);
        $stmt->bindValue('description', $description, PDO::PARAM_STR);
        $stmt->bindValue('deduced_description', $deducedDescription, PDO::PARAM_STR);
        $stmt->bindValue('content', $content, PDO::PARAM_STR);
        $stmt->bindValue('url_slug', $urlSlug, PDO::PARAM_STR);
        $stmt->bindValue('url_id', $urlId, PDO::PARAM_STR);
        $stmt->bindValue('knowledgebase_id', $knowledgebaseId, PDO::PARAM_STR);
        $stmt->bindValue('organization_id', $organizationId, PDO::PARAM_STR);
        $stmt->bindValue('section_id', $sectionId, PDO::PARAM_STR);
        $stmt->bindValue('created_by', $createdBy, PDO::PARAM_STR);
        $stmt->bindValue('last_updated_by', $createdBy, PDO::PARAM_STR);
        $stmt->bindValue('id', $id, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetch();
    }
}