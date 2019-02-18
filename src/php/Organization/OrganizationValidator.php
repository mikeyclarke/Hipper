<?php
declare(strict_types=1);

namespace Lithos\Organization;

use Lithos\Validation\ConstraintViolationListFormatter;
use Lithos\Validation\Constraints\NotReservedSubdomain;
use Lithos\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validation;

class OrganizationValidator
{
    public function validate(array $input): void
    {
        $this->validateInput($input);
    }

    private function validateInput(array $input): void
    {
        $constraints = [
            'name' => [
                new Optional([
                    new NotBlank,
                    new Length([
                        'min' => 3,
                        'max' => 50,
                    ]),
                ]),
            ],
            'subdomain' => [
                new Optional([
                    new NotBlank,
                    new Length([
                        'min' => 3,
                        'max' => 63,
                    ]),
                    new Regex([
                        'pattern' => '/[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])?/',
                    ]),
                    new NotReservedSubdomain,
                ]),
            ],
        ];

        $validator = Validation::createValidator();
        $collectionConstraint = new Collection($constraints);

        $violations = $validator->validate($input, $collectionConstraint);

        if (count($violations) > 0) {
            throw new ValidationException(
                ConstraintViolationListFormatter::format($violations)
            );
        }
    }
}
