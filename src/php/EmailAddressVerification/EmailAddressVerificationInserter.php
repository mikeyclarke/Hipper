<?php
namespace hleo\EmailAddressVerification;

use Doctrine\DBAL\Connection;

class EmailAddressVerificationInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(string $id, string $personId, string $hash, string $expires)
    {
        $this->connection->insert(
            'email_address_verification',
            [
                'id' => $id,
                'person_id' => $personId,
                'hash' => $hash,
                'expires' => $expires,
            ]
        );
    }
}
