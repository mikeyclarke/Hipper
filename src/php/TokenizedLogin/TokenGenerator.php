<?php
declare(strict_types=1);

namespace Lithos\TokenizedLogin;

use RandomLib\Factory;

class TokenGenerator
{
    public function generate(): string
    {
        $factory = new Factory;
        $generator = $factory->getMediumStrengthGenerator();
        $token = $generator->generateString(32);
        return $token;
    }
}
