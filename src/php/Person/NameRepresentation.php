<?php
declare(strict_types=1);

namespace Lithos\Person;

class NameRepresentation
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function full(): string
    {
        return $this->name;
    }

    public function abbreviated(): ?string
    {
        preg_match_all('/(\S)\S+/', $this->name, $matches, PREG_PATTERN_ORDER);
        if (empty($matches[1])) {
            return null;
        }
        return implode('', $matches[1]);
    }

    public function possessive(): string
    {
        return $this->name . '’s';
    }
}
