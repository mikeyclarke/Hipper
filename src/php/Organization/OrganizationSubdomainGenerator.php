<?php
declare(strict_types=1);

namespace Lithos\Organization;

use Lithos\Validation\Constraints\NotReservedSubdomain;
use Lithos\Validation\Constraints\UniqueSubdomain;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrganizationSubdomainGenerator
{
    private $validatorInterface;

    public function __construct(
        ValidatorInterface $validatorInterface
    ) {
        $this->validatorInterface = $validatorInterface;
    }

    public function generate(string $organizationName): string
    {
        $subdomain = $this->stripInvalidCharacters($organizationName);
        $subdomain = $this->replaceSpaces($subdomain);
        $subdomain = $this->stripDuplicateDashes($subdomain);
        $subdomain = $this->stripOuterDashes($subdomain);
        $subdomain = $this->toLowercase($subdomain);
        $subdomain = $this->checkIsNotTakenAndNotBlacklisted($subdomain);
        return $subdomain;
    }

    private function stripInvalidCharacters(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9-\s]/', '', $value);
    }

    private function stripDuplicateDashes(string $value): string
    {
        return preg_replace('/-+/', '-', $value);
    }

    private function stripOuterDashes(string $value): string
    {
        return trim($value, "-");
    }

    private function replaceSpaces(string $value): string
    {
        return preg_replace('/\s/', '-', $value);
    }

    private function toLowercase(string $value): string
    {
        return strtolower($value);
    }

    private function checkIsNotTakenAndNotBlacklisted(string $value): string
    {
        $violations = $this->validatorInterface->validate($value, [
            new NotReservedSubdomain,
            new UniqueSubdomain,
        ]);
        if (count($violations) > 0) {
            return '';
        }
        return $value;
    }
}
