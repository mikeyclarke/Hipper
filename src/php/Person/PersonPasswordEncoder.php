<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\Person\Exception\PasswordUnsafeForProcessingException;

class PersonPasswordEncoder
{
    const MAX_LENGTH = 4096;

    public function encodePassword(string $rawPassword): string
    {
        if (!defined('PASSWORD_ARGON2ID')) {
            throw new \Exception('Argon2id password hashing is not supported in this environment');
        }

        if ($this->passwordIsTooLong($rawPassword)) {
            throw new PasswordUnsafeForProcessingException;
        }

        $result = password_hash($rawPassword, \PASSWORD_ARGON2ID);

        return $result;
    }

    public function isPasswordValid(string $encodedPassword, string $rawPassword): bool
    {
        if (!defined('PASSWORD_ARGON2ID')) {
            throw new \Exception('Argon2id password hashing is not supported in this environment');
        }

        if ($this->passwordIsTooLong($rawPassword)) {
            return false;
        }

        return password_verify($rawPassword, $encodedPassword);
    }

    private function passwordIsTooLong(string $rawPassword): bool
    {
        return strlen($rawPassword) > self::MAX_LENGTH;
    }
}
