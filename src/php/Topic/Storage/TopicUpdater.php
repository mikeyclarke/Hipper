<?php
declare(strict_types=1);

namespace Hipper\Topic\Storage;

use Doctrine\DBAL\Connection;
use PDO;

class TopicUpdater
{
    private const FIELDS_TO_RETURN = [
        'name',
        'description',
        'url_slug',
        'parent_topic_id',
        'updated',
    ];

    private const UPDATE_FIELDS_WHITELIST = [
        'name',
        'description',
        'url_slug',
        'parent_topic_id',
    ];

    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function update(string $id, array $parameters): array
    {
        $fieldsToUpdate = array_intersect_key($parameters, array_flip(self::UPDATE_FIELDS_WHITELIST));
        $fieldsToReturn = implode(', ', self::FIELDS_TO_RETURN);

        $sql = 'UPDATE topic SET ';
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
