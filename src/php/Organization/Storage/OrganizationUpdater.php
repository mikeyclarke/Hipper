<?php
declare(strict_types=1);

namespace Hipper\Organization\Storage;

use Doctrine\DBAL\Connection;
use PDO;

class OrganizationUpdater
{
    private const FIELDS = [
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
        $fieldsToUpdate = array_intersect_key($parameters, array_flip(array_keys(self::FIELDS)));

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
        $sql .= ' RETURNING *';

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue('id', $id, PDO::PARAM_STR);
        foreach ($fieldsToUpdate as $name => $value) {
            $type = self::FIELDS[$name];
            $stmt->bindValue($name, $value, $type);
        }

        $stmt->execute();
        return $stmt->fetch();
    }
}
