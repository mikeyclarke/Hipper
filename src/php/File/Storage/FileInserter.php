<?php

declare(strict_types=1);

namespace Hipper\File\Storage;

use Doctrine\DBAL\Connection;
use PDO;

class FileInserter
{
    private const DEFAULT_FIELDS = [
        'id',
        'storage_path',
        'type',
        'mime_type',
        'usage',
        'organization_id',
    ];

    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(
        string $id,
        string $contentHash,
        string $storagePath,
        string $fileType,
        string $mimeType,
        string $usage,
        int $bytes,
        ?int $height,
        ?int $width,
        string $organizationId,
        string $creatorId
    ): array {
        $fieldsToReturn = implode(', ', self::DEFAULT_FIELDS);

        $sql = <<<SQL
INSERT INTO file (
    id, content_hash, storage_path, file_type, mime_type, usage, bytes, height, width, organization_id, creator_id
) VALUES (
    :id, :content_hash, :storage_path, :file_type, :mime_type, :usage, :bytes, :height, :width, :organization_id,
    :creator_id
) RETURNING $fieldsToReturn
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id, PDO::PARAM_STR);
        $stmt->bindValue('content_hash', $contentHash, PDO::PARAM_STR);
        $stmt->bindValue('storage_path', $storagePath, PDO::PARAM_STR);
        $stmt->bindValue('file_type', $fileType, PDO::PARAM_STR);
        $stmt->bindValue('mime_type', $mimeType, PDO::PARAM_STR);
        $stmt->bindValue('usage', $usage, PDO::PARAM_STR);
        $stmt->bindValue('bytes', $bytes, PDO::PARAM_INT);
        $stmt->bindValue('height', $height, PDO::PARAM_INT);
        $stmt->bindValue('width', $width, PDO::PARAM_INT);
        $stmt->bindValue('organization_id', $organizationId, PDO::PARAM_STR);
        $stmt->bindValue('creator_id', $creatorId, PDO::PARAM_STR);

        $statementResult = $stmt->execute();
        return $statementResult->fetchAssociative();
    }
}
