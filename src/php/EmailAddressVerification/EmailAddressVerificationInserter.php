<?php
declare(strict_types=1);

namespace Lithos\EmailAddressVerification;

use Doctrine\DBAL\Connection;

class EmailAddressVerificationInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(string $id, string $personId, string $verificationPhrase, string $expires): void
    {
        $this->connection->insert(
            'email_address_verification',
            [
                'id' => $id,
                'person_id' => $personId,
                'verification_phrase' => $verificationPhrase,
                'expires' => $expires,
            ]
        );
    }
}
