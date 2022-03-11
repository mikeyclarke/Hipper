<?php

declare(strict_types=1);

namespace Hipper\File;

use Doctrine\DBAL\Connection;

class FileRepository
{
    private const DEFAULT_FIELDS = [
        'id',
        'storage_path',
        'file_type',
        'mime_type',
        'usage',
        'height',
        'width',
    ];

    private Connection $connection;

    public function __construct(
        Connection $connection,
    ) {
        $this->connection = $connection;
    }

    public function findById(string $id, array $additionalFields = []): ?array
    {
        $qb = $this->connection->createQueryBuilder();

        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb->select($fields)
            ->from('file')
            ->where('id = :id');

        $qb->setParameter('id', $id);

        $statementResult = $qb->execute();
        $result = $statementResult->fetchAssociative();

        if (false === $result) {
            return null;
        }

        return $result;
    }
}
