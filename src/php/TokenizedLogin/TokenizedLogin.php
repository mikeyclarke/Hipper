<?php
declare(strict_types=1);

namespace Lithos\TokenizedLogin;

use Lithos\IdGenerator\IdGenerator;
use Lithos\Person\PersonModel;

class TokenizedLogin
{
    private $idGenerator;
    private $tokenGenerator;
    private $tokenInserter;

    public function __construct(
        IdGenerator $idGenerator,
        TokenGenerator $tokenGenerator,
        TokenInserter $tokenInserter
    ) {
        $this->idGenerator = $idGenerator;
        $this->tokenGenerator = $tokenGenerator;
        $this->tokenInserter = $tokenInserter;
    }

    public function create(PersonModel $person): string
    {
        $tokenId = $this->idGenerator->generate();
        $token = $this->tokenGenerator->generate();
        $expiryDate = new \DateTime('+ 1 hours');

        $this->tokenInserter->insert(
            $tokenId,
            $person->getId(),
            $token,
            $expiryDate->format('Y-m-d H:i:s')
        );

        return $token;
    }
}
