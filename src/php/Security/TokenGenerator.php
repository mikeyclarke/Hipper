<?php
declare(strict_types=1);

namespace Hipper\Security;

use RandomLib\Factory;

class TokenGenerator
{
    private const CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const LENGTH = 32;

    public function generate(): string
    {
        $factory = new Factory;
        $generator = $factory->getMediumStrengthGenerator();
        $token = $generator->generateString(self::LENGTH, self::CHARACTERS);
        return $token;
    }
}
