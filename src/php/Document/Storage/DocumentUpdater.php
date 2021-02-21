<?php
declare(strict_types=1);

namespace Hipper\Document\Storage;

use Doctrine\DBAL\Connection;
use PDO;

class DocumentUpdater
{
    private const FIELDS_TO_RETURN = [
        'name',
        'description',
        'deduced_description',
        'content',
        'url_slug',
        'topic_id',
        'last_updated_by',
        'updated',
    ];

    private const UPDATE_FIELDS_WHITELIST = [
        'name',
        'description',
        'deduced_description',
        'content',
        'content_plain',
        'url_slug',
        'topic_id',
        'last_updated_by',
    ];

    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function update(string $id, array $parameters): array
    {
        $fieldsToUpdate = array_intersect_key($parameters, array_flip(self::UPDATE_FIELDS_WHITELIST));
        $fieldsToReturn = implode(', ', self::FIELDS_TO_RETURN);

        $sql = 'UPDATE document SET ';
        $sql .= implode(
            ', ',
            array_map(
                function ($field) {
                    return sprintf('%s = :%s', $field, $field);
                },
                array_keys($fieldsToUpdate)
            )
        );
        $sql .= ' WHERE id = :id';
        $sql .= " RETURNING {$fieldsToReturn}";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue('id', $id, PDO::PARAM_STR);
        foreach ($fieldsToUpdate as $name => $value) {
            $stmt->bindValue($name, $value, PDO::PARAM_STR);
        }

        $statementResult = $stmt->execute();
        return $statementResult->fetchAssociative();
    }
}
