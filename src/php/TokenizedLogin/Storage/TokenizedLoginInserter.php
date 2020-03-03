<?php
declare(strict_types=1);

namespace Hipper\TokenizedLogin\Storage;

use Doctrine\DBAL\Connection;

class TokenizedLoginInserter
{
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function insert(string $id, string $personId, string $token, string $expires): void
    {
        $this->connection->insert(
            'tokenized_login',
            [
                'id' => $id,
                'person_id' => $personId,
                'token' => $token,
                'expires' => $expires,
            ]
        );
    }
}