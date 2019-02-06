<?php
declare(strict_types=1);

namespace Lithos\Organization;

use Lithos\Validation\ConstraintViolationListFormatter;
use Lithos\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
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
                new NotBlank,
                new Length([
                    'min' => 3,
                    'max' => 50,
                ]),
            ],
            'subdomain' => [
                new NotBlank,
                new Length([
                    'min' => 3,
                    'max' => 63,
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