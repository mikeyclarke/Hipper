<?php
declare(strict_types=1);

namespace Hipper\Organization\Storage;

use Doctrine\DBAL\Connection;
use PDO;

class OrganizationUpdater
{
    private const FIELDS_TO_RETURN = [
        'name',
        'knowledgebase_id',
        'subdomain',
        'approved_email_domain_signup_allowed',
        'approved_email_domains',
        'updated',
    ];
    private const UPDATE_FIELDS_WHITELIST = [
        'name' => PDO::PARAM_STR,
        'knowledgebase_id' => PDO::PARAM_STR,
        'subdomain' => PDO::PARAM_STR,
        'approved_email_domain_signup_allowed' => PDO::PARAM_BOOL,
        'approved_email_domains' => PDO::PARAM_STR,
    ];

    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function update(string $id, array $parameters): array
    {
        $fieldsToUpdate = array_intersect_key($parameters, array_flip(array_keys(self::UPDATE_FIELDS_WHITELIST)));
        $fieldsToReturn = implode(', ', self::FIELDS_TO_RETURN);

        $sql = 'UPDATE organization SET ';
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
            $type = self::UPDATE_FIELDS_WHITELIST[$name];
            $stmt->bindValue($name, $value, $type);
        }

        $stmt->execute();
        return $stmt->fetch();
    }
}
