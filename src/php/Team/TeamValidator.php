<?php
declare(strict_types=1);

namespace Lithos\Team;

use Lithos\Validation\ConstraintViolationListFormatter;
use Lithos\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Validation;

class TeamValidator
{
    public function validate(array $input, bool $isNew = false): void
    {
        $this->validateInput($input, $isNew);

        // Check name isn’t already taken
    }

    private function validateInput(array $input, bool $isNew): void
    {
        $requiredOnCreate = ['name'];
        $constraints = [
            'name' => [
                new NotBlank,
                new Length([
                    'min' => 3,
                    'max' => 100,
                ]),
            ],
            'description' => [
                new Length([
                    'max' => 300,
                ]),
            ],
        ];

        foreach ($constraints as $key => &$value) {
            if (in_array($key, $requiredOnCreate) && $isNew) {
                $constraints[$key] = [new Required($constraints[$key])];
            } else {
                $constraints[$key] = [new Optional($constraints[$key])];
            }
        }

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
