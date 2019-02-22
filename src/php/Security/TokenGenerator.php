<?php
declare(strict_types=1);

namespace Lithos\Security;

use RandomLib\Factory;

class TokenGenerator
{
    const CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function generate(): string
    {
        $factory = new Factory;
        $generator = $factory->getMediumStrengthGenerator();
        $token = $generator->generateString(32, self::CHARACTERS);
        return $token;
    }
}
