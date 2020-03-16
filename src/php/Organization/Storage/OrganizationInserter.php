<?php
declare(strict_types=1);

namespace Hipper\Organization\Storage;

use Doctrine\DBAL\Connection;

class OrganizationInserter
{
    private const FIELDS_TO_RETURN = [
        'id',
        'knowledgebase_id',
        'name',
        'subdomain',
        'approved_email_domain_signup_allowed',
        'approved_email_domains',
        'created',
        'updated',
    ];

    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(string $id, string $name): array
    {
        $fieldsToReturn = implode(', ', self::FIELDS_TO_RETURN);
        $sql = "INSERT INTO organization (id, name) VALUES (:id, :name) RETURNING {$fieldsToReturn}";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id);
        $stmt->bindValue('name', $name);
        $stmt->execute();
        return $stmt->fetch();
    }
}
