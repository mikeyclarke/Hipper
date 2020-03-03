<?php
declare(strict_types=1);

namespace Hipper\Person\Storage;

use Doctrine\DBAL\Connection;
use PDO;

class PersonInserter
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(
        string $id,
        string $name,
        string $abbreviatedName,
        string $emailAddress,
        string $password,
        string $urlId,
        string $username,
        string $organizationId,
        bool $emailAddressVerified
    ): ?array {
        $sql = <<<SQL
INSERT INTO person
(
    id,
    name,
    abbreviated_name,
    email_address,
    password,
    url_id,
    username,
    organization_id,
    email_address_verified
)
VALUES
(
    :id,
    :name,
    :abbreviated_name,
    :email_address,
    :password,
    :url_id,
    :username,
    :organization_id,
    :email_address_verified
)
RETURNING *
SQL;
        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue('id', $id, PDO::PARAM_STR);
        $stmt->bindValue('name', $name, PDO::PARAM_STR);
        $stmt->bindValue('abbreviated_name', $abbreviatedName, PDO::PARAM_STR);
        $stmt->bindValue('email_address', $emailAddress, PDO::PARAM_STR);
        $stmt->bindValue('password', $password, PDO::PARAM_STR);
        $stmt->bindValue('url_id', $urlId, PDO::PARAM_STR);
        $stmt->bindValue('username', $username, PDO::PARAM_STR);
        $stmt->bindValue('organization_id', $organizationId, PDO::PARAM_STR);
        $stmt->bindValue('email_address_verified', $emailAddressVerified, PDO::PARAM_BOOL);

        $stmt->execute();
        $result = $stmt->fetch();

        if (false === $result) {
            return null;
        }

        return $result;
    }
}