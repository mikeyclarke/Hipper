<?php
declare(strict_types=1);

namespace Lithos\Organization;

use Lithos\Validation\Constraints\NotReservedSubdomain;
use Symfony\Component\Validator\Validation;

class OrganizationSubdomainGenerator
{
    public function generate(string $organizationName): string
    {
        $subdomain = $this->stripInvalidCharacters($organizationName);
        $subdomain = $this->stripDuplicateWhitespace($subdomain);
        $subdomain = $this->stripOuterWhitespaceAndDashes($subdomain);
        $subdomain = $this->replaceSpaces($subdomain);
        $subdomain = $this->toLowercase($subdomain);
        $subdomain = $this->checkAgainstBlacklist($subdomain);
        return $subdomain;
    }

    private function stripInvalidCharacters(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9-\s]/', '', $value);
    }

    private function stripDuplicateWhitespace(string $value): string
    {
        return preg_replace('/\s+/', ' ', $value);
    }

    private function stripOuterWhitespaceAndDashes(string $value): string
    {
        return trim($value, "- \t\n\r\0\x0B");
    }

    private function replaceSpaces(string $value): string
    {
        return preg_replace('/\s/', '-', $value);
    }

    private function toLowercase(string $value): string
    {
        return strtolower($value);
    }

    private function checkAgainstBlacklist(string $value): string
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($value, [new NotReservedSubdomain]);
        if (count($violations) > 0) {
            return '';
        }
        return $value;
    }
}
