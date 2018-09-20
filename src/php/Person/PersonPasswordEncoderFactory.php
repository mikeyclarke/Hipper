<?php
declare(strict_types=1);

namespace Lithos\Person;

use Symfony\Component\Security\Core\Encoder\Argon2iPasswordEncoder;

class PersonPasswordEncoderFactory
{
    public function create(): Argon2iPasswordEncoder
    {
        return new Argon2iPasswordEncoder;
    }
}
