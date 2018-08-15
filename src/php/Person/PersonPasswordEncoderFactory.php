<?php
namespace hleo\Person;

use Symfony\Component\Security\Core\Encoder\Argon2iPasswordEncoder;

class PersonPasswordEncoderFactory
{
    public function create()
    {
        return new Argon2iPasswordEncoder;
    }
}
