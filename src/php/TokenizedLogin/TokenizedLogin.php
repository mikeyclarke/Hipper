<?php
declare(strict_types=1);

namespace Lithos\TokenizedLogin;

use Lithos\IdGenerator\IdGenerator;
use Lithos\Person\PersonModel;
use Lithos\Security\TokenGenerator;

class TokenizedLogin
{
    private $idGenerator;
    private $tokenGenerator;
    private $tokenizedLoginInserter;

    public function __construct(
        IdGenerator $idGenerator,
        TokenGenerator $tokenGenerator,
        TokenizedLoginInserter $tokenizedLoginInserter
    ) {
        $this->idGenerator = $idGenerator;
        $this->tokenGenerator = $tokenGenerator;
        $this->tokenizedLoginInserter = $tokenizedLoginInserter;
    }

    public function create(PersonModel $person): string
    {
        $tokenId = $this->idGenerator->generate();
        $token = $this->tokenGenerator->generate();
        $expiryDate = new \DateTime('+ 1 hours');

        $this->tokenizedLoginInserter->insert(
            $tokenId,
            $person->getId(),
            $token,
            $expiryDate->format('Y-m-d H:i:s')
        );

        return $token;
    }
}
