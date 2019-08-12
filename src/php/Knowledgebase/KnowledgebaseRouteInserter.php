<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Doctrine\DBAL\Connection;
use PDO;

class KnowledgebaseRouteInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(
        string $id,
        string $urlId,
        string $route,
        string $entity,
        string $organizationId,
        string $knowledgebaseId,
        string $sectionId = null,
        string $documentId = null,
        bool $isCanonical = true
    ): array {
        $sql = <<<SQL
INSERT INTO knowledgebase_route
(id, url_id, route, is_canonical, entity, organization_id, knowledgebase_id, section_id, document_id)
VALUES (:id, :url_id, :route, :is_canonical, :entity, :organization_id, :knowledgebase_id, :section_id, :document_id)
RETURNING *
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id, PDO::PARAM_STR);
        $stmt->bindValue('url_id', $urlId, PDO::PARAM_STR);
        $stmt->bindValue('route', $route, PDO::PARAM_STR);
        $stmt->bindValue('is_canonical', $isCanonical, PDO::PARAM_BOOL);
        $stmt->bindValue('entity', $entity, PDO::PARAM_STR);
        $stmt->bindValue('organization_id', $organizationId, PDO::PARAM_STR);
        $stmt->bindValue('knowledgebase_id', $knowledgebaseId, PDO::PARAM_STR);
        $stmt->bindValue('section_id', $sectionId, PDO::PARAM_STR);
        $stmt->bindValue('document_id', $documentId, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetch();
    }
}
